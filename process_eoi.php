<?php
// 1. Only process submissions from the application form.
if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST" || empty($_POST)) {
    header("Location: apply.php", true, 303);
    exit();
}

// Results contain personal information and should not be cached.
header("Cache-Control: no-store");
header("Content-Type: text/html; charset=UTF-8");

// 2. Read text safely: a forged array must never reach a string function.
function sanitise_input($field, $label, &$errors) {
    if (!isset($_POST[$field])) {
        return "";
    }

    if (!is_string($_POST[$field])) {
        $errors[] = $label . " must be a single text value.";
        return "";
    }

    $data = trim(stripslashes($_POST[$field]));
    if (!preg_match("//u", $data) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $data)) {
        $errors[] = $label . " contains invalid text characters.";
        return "";
    }

    return $data;
}

$errors = array();
$first_name = sanitise_input("first_name", "First name", $errors);
$last_name = sanitise_input("last_name", "Last name", $errors);
$dob = sanitise_input("dob", "Date of birth", $errors);
$address = sanitise_input("address", "Street address", $errors);
$sub_town = sanitise_input("sub_town", "Suburb or town", $errors);
$state = sanitise_input("state", "State", $errors);
$postcode = sanitise_input("postcode", "Postcode", $errors);
$email = sanitise_input("email", "Email", $errors);
$phone_num = sanitise_input("phone_num", "Phone number", $errors);
$gender = sanitise_input("gender", "Gender", $errors);
$job_ref_num = sanitise_input("job_ref_num", "Job reference number", $errors);
$skill_other = sanitise_input("skill_other", "Other skills", $errors);

// Reject unrelated POST requests, but show errors for incomplete applications.
$form_fields = array(
    "first_name", "last_name", "dob", "address", "sub_town", "state",
    "postcode", "email", "phone_num", "gender", "job_ref_num", "skills", "skill_other"
);
if (count(array_intersect($form_fields, array_keys($_POST))) === 0) {
    header("Location: apply.php", true, 303);
    exit();
}

// 3. Validate the rules in apply.php and the storage limits in eoi.sql.
if (!preg_match('/\A[a-zA-Z]{1,20}\z/', $first_name)) {
    $errors[] = "First name is required and must contain 1 to 20 letters (A-Z) only.";
}
if (!preg_match('/\A[a-zA-Z]{1,20}\z/', $last_name)) {
    $errors[] = "Last name is required and must contain 1 to 20 letters (A-Z) only.";
}

$dob_database = "";
if (!preg_match('/\A[0-9]{2}\/[0-9]{2}\/[0-9]{4}\z/', $dob)) {
    $errors[] = "Date of birth is required in dd/mm/yyyy format.";
} else {
    $date = explode("/", $dob);
    if (!checkdate((int)$date[1], (int)$date[0], (int)$date[2])) {
        $errors[] = "Date of birth must be a real calendar date.";
    } else {
        // Keep the existing group's YYYY-MM-DD convention for its VARCHAR(10) column.
        $dob_database = $date[2] . "-" . $date[1] . "-" . $date[0];
    }
}

// Count UTF-8 characters, rather than bytes, for VARCHAR character limits.
if ($address === "" || mb_strlen($address, "UTF-8") > 40) {
    $errors[] = "Street address is required and must not exceed 40 characters.";
}
if ($sub_town === "" || mb_strlen($sub_town, "UTF-8") > 40) {
    $errors[] = "Suburb or town is required and must not exceed 40 characters.";
}

$valid_states = array("VIC", "NSW", "QLD", "NT", "WA", "SA", "TAS", "ACT");
if (!in_array($state, $valid_states, true)) {
    $errors[] = "Please select a valid state or territory.";
}
if (!preg_match('/\A[0-9]{4}\z/', $postcode)) {
    $errors[] = "Postcode must contain exactly 4 digits.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = "Email must be a valid email address of no more than 100 characters.";
}
if (!preg_match('/\A[0-9]{8,12}\z/', $phone_num)) {
    $errors[] = "Phone number must contain 8 to 12 digits, without spaces or punctuation.";
}

$valid_genders = array("Male", "female", "not_said");
if (!in_array($gender, $valid_genders, true)) {
    $errors[] = "Please select a valid gender option.";
}
// The existing form permits letters, digits and internal spaces in a five-character reference.
if (!preg_match('/\A[a-zA-Z0-9 ]{5}\z/', $job_ref_num)) {
    $errors[] = "Job reference number must contain exactly 5 letters, digits or internal spaces.";
}

// 4. Skills are optional; validate every selected value before joining the list.
$valid_skills = array(
    "Solar_Wind_Systems", "Smart_Grid_Storage", "SCADA_EMS",
    "Carbon_Accounting", "Public_Engagement", "Other"
);
$skills = array();
if (isset($_POST["skills"])) {
    if (!is_array($_POST["skills"]) || count($_POST["skills"]) > count($valid_skills)) {
        $errors[] = "Please submit a valid list of skills.";
    } else {
        foreach ($_POST["skills"] as $key => $skill) {
            if (!is_int($key) || !is_string($skill)) {
                $errors[] = "Each skill must be a single checkbox selection.";
                continue;
            }
            $skill = trim(stripslashes($skill));
            if (!in_array($skill, $valid_skills, true)) {
                $errors[] = "Please select skills from the available checkboxes only.";
            } elseif (in_array($skill, $skills, true)) {
                $errors[] = "Each skill may be selected only once.";
            } else {
                $skills[] = $skill;
            }
        }
    }
}
if (in_array("Other", $skills, true) && $skill_other === "") {
    $errors[] = "Please describe your other skills when Other skills is selected.";
}
// MySQL TEXT holds at most 65,535 bytes, including multibyte UTF-8 text.
if (strlen($skill_other) > 65535) {
    $errors[] = "Other skills is too long; please shorten it (maximum 65,535 UTF-8 bytes).";
}
$skills_text = implode(", ", $skills);

// 5. Only fully valid applications may connect to the database and be inserted.
$eoi_number = null;
$database_failed = false;
$conn = null;
$stmt = null;
if (count($errors) === 0) {
    // Throw catchable exceptions, including connection failures inside settings.php.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        require_once __DIR__ . '/settings.php';

        // Match eoi.sql. This leaves an existing table and its records untouched.
        $sql = "CREATE TABLE IF NOT EXISTS eoi (
            eoi_number INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            job_ref_num VARCHAR(5) NOT NULL,
            first_name VARCHAR(20) NOT NULL,
            last_name VARCHAR(20) NOT NULL,
            dob VARCHAR(10) NOT NULL,
            gender VARCHAR(15) NOT NULL,
            street_address VARCHAR(40) NOT NULL,
            suburb_town VARCHAR(40) NOT NULL,
            state VARCHAR(3) NOT NULL,
            postcode VARCHAR(4) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone_num VARCHAR(15) NOT NULL,
            skills VARCHAR(255) DEFAULT NULL,
            other_skills TEXT DEFAULT NULL,
            status ENUM('New','Current','Final') NOT NULL DEFAULT 'New'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        mysqli_query($conn, $sql);

        // Store plain text; placeholders protect SQL. Escape only when displaying HTML.
        // Omitting status lets the database apply its New default.
        $query = "INSERT INTO eoi (
            first_name, last_name, dob, street_address, suburb_town, state,
            postcode, email, phone_num, gender, job_ref_num, skills, other_skills
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt, "sssssssssssss", $first_name, $last_name, $dob_database,
            $address, $sub_town, $state, $postcode, $email, $phone_num,
            $gender, $job_ref_num, $skills_text, $skill_other
        );
        mysqli_stmt_execute($stmt);
        $eoi_number = mysqli_insert_id($conn);
    } catch (mysqli_sql_exception $exception) {
        $database_failed = true;
        // Log only the error code, not credentials, SQL or applicants' personal details.
        error_log("process_eoi.php: database operation failed (code " . $exception->getCode() . ").");
    } finally {
        if ($stmt !== null && $stmt !== false) {
            mysqli_stmt_close($stmt);
        }
        if ($conn !== null && $conn !== false) {
            mysqli_close($conn);
        }
    }
}

// 6. Render one complete result page using the group's shared layout.
if (count($errors) > 0) {
    http_response_code(422);
} elseif ($database_failed) {
    http_response_code(503);
}
include __DIR__ . '/header.inc';
?>
<main>
    <?php if (count($errors) > 0): ?>
        <h1>Application needs correction</h1>
        <section aria-labelledby="validation-heading">
            <h2 id="validation-heading">Please check your details</h2>
            <p>Your application has not been saved. Correct the following items and submit again:</p>
            <ul>
                <?php foreach (array_unique($errors) as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Use your browser's Back button to return to your entered details, or open the application form below.</p>
            <p><a href="apply.php">Return to application form</a></p>
        </section>
    <?php elseif ($database_failed): ?>
        <h1>Application could not be completed</h1>
        <p>We could not confirm that your application was saved because the application service is temporarily unavailable.</p>
        <p>Please try again later. If the problem continues, contact us using the email in the footer.</p>
        <p><a href="apply.php">Return to application form</a></p>
    <?php else: ?>
        <h1>Application successful!</h1>
        <section aria-labelledby="confirmation-heading">
            <h2 id="confirmation-heading">Thank you, <?php echo htmlspecialchars($first_name, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?>.</h2>
            <p>Your expression of interest has been saved successfully.</p>
            <div class="highlightbox">
                <p>Your EOI number is: <strong><?php echo htmlspecialchars((string)$eoi_number, ENT_QUOTES, "UTF-8"); ?></strong></p>
                <p>Job reference: <?php echo htmlspecialchars($job_ref_num, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?></p>
                <p>Application status: <strong>New</strong></p>
            </div>
            <p>Please keep your EOI number for future enquiries. Refreshing this page may submit another application.</p>
            <p><a href="apply.php">Submit another application</a></p>
        </section>
    <?php endif; ?>
    <p><a href="index.php">Return to homepage</a></p>
<?php
// footer.inc closes the main element and the HTML document.
include __DIR__ . '/footer.inc';
?>
