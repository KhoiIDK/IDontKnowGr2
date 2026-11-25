<?php
// 1. Prevent direct URL access
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: apply.php");
    exit();
}

// 2. Function to sanitise input
function sanitise_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// 3. Receive and sanitise form data
$first_name = sanitise_input($_POST["first_name"] ?? "");
$last_name = sanitise_input($_POST["last_name"] ?? "");
$dob = sanitise_input($_POST["dob"] ?? "");
$address = sanitise_input($_POST["address"] ?? "");
$sub_town = sanitise_input($_POST["sub_town"] ?? "");
$state = sanitise_input($_POST["state"] ?? "");
$postcode = sanitise_input($_POST["postcode"] ?? "");
$email = sanitise_input($_POST["email"] ?? "");
$phone_num = sanitise_input($_POST["phone_num"] ?? "");
$gender = sanitise_input($_POST["gender"] ?? "");
$job_ref_num = sanitise_input($_POST["job_ref_num"] ?? "");
$skill_other = sanitise_input($_POST["skill_other"] ?? "");

$skills = $_POST["skills"] ?? array();

// 4. Server-side validation
$errors = array();

// First name
if (!preg_match("/^[a-zA-Z]{1,20}$/", $first_name)) {
    $errors[] = "First name must contain only letters and be no more than 20 characters.";
}

// Last name
if (!preg_match("/^[a-zA-Z]{1,20}$/", $last_name)) {
    $errors[] = "Last name must contain only letters and be no more than 20 characters.";
}

// Date of birth
if (!preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $dob)) {
    $errors[] = "Date of birth must be in dd/mm/yyyy format.";
} else {
    $date = explode("/", $dob);

    if (!checkdate((int)$date[1], (int)$date[0], (int)$date[2])) {
        $errors[] = "Date of birth is invalid.";
    }
}

// Street address
if ($address == "" || strlen($address) > 40) {
    $errors[] = "Street address is required and must not exceed 40 characters.";
}

// Suburb or town
if ($sub_town == "" || strlen($sub_town) > 40) {
    $errors[] = "Suburb or town is required and must not exceed 40 characters.";
}

// State
$valid_states = array("VIC", "NSW", "QLD", "NT", "WA", "SA", "TAS", "ACT");

if (!in_array($state, $valid_states)) {
    $errors[] = "Please select a valid state.";
}

// Postcode
if (!preg_match("/^[0-9]{4}$/", $postcode)) {
    $errors[] = "Postcode must contain exactly 4 digits.";
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = "Please enter a valid email address.";
}

// Phone number
if (!preg_match("/^[0-9]{8,12}$/", $phone_num)) {
    $errors[] = "Phone number must contain 8 to 12 digits.";
}

// Gender
$valid_genders = array("Male", "female", "not_said");

if (!in_array($gender, $valid_genders)) {
    $errors[] = "Please select a valid gender.";
}

// Job reference number
if (!preg_match("/^[a-zA-Z0-9 ]{5}$/", $job_ref_num)) {
    $errors[] = "Job reference number must contain exactly 5 permitted characters.";
}

// Skills
$valid_skills = array(
    "Solar_Wind_Systems",
    "Smart_Grid_Storage",
    "SCADA_EMS",
    "Carbon_Accounting",
    "Public_Engagement",
    "Other"
);

if (!is_array($skills)) {
    $errors[] = "Invalid skills selection.";
} else {
    foreach ($skills as $skill) {
        if (!in_array($skill, $valid_skills, true)) {
            $errors[] = "Invalid skill selected.";
        }
    }

    if (in_array("Other", $skills) && $skill_other == "") {
        $errors[] = "Please specify your other skills.";
    }
}

// 5. Display validation errors
if (count($errors) > 0) {
    include 'header.inc';

    echo "<h1>Application unsuccessful</h1>";
    echo "<p>Please correct the following errors:</p>";
    echo "<ul>";

    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }

    echo "</ul>";
    echo '<p><a href="apply.php">Return to application form</a></p>';

    include 'footer.inc';
    exit();
}

// Convert skills array into a string
$skills_text = implode(", ", $skills);

// Convert date of birth into MySQL date format
$dob = $date[2] . "-" . $date[1] . "-" . $date[0];

// 6. Connect to the database
require_once 'settings.php';

// 7. Create EOI table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS eoi (
    EOInumber INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(20) NOT NULL,
    last_name VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    address VARCHAR(40) NOT NULL,
    sub_town VARCHAR(40) NOT NULL,
    state VARCHAR(3) NOT NULL,
    postcode VARCHAR(4) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone_num VARCHAR(12) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    job_ref_num VARCHAR(5) NOT NULL,
    skills TEXT,
    skill_other TEXT,
    status ENUM('New', 'Current', 'Final') DEFAULT 'New'
)";

if (!mysqli_query($conn, $sql)) {
    die("Unable to create EOI table.");
}

// 8. Insert the application into the database
$query = "INSERT INTO eoi (
    first_name,
    last_name,
    dob,
    address,
    sub_town,
    state,
    postcode,
    email,
    phone_num,
    gender,
    job_ref_num,
    skills,
    skill_other
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Unable to prepare application.");
}

mysqli_stmt_bind_param(
    $stmt,
    "sssssssssssss",
    $first_name,
    $last_name,
    $dob,
    $address,
    $sub_town,
    $state,
    $postcode,
    $email,
    $phone_num,
    $gender,
    $job_ref_num,
    $skills_text,
    $skill_other
);

// 9. Display application confirmation
include 'header.inc';

if (mysqli_stmt_execute($stmt)) {
    $eoi_number = mysqli_insert_id($conn);

    echo "<h1>Application successful!</h1>";
    echo "<p>Thank you, " . htmlspecialchars($first_name) . ".</p>";
    echo "<p>Your application has been successfully submitted.</p>";
    echo "<p>Your EOI number is: <strong>" . $eoi_number . "</strong></p>";
    echo "<p>Application status: New</p>";
    echo '<p><a href="index.php">Return to homepage</a></p>';
} else {
    echo "<h1>Application unsuccessful</h1>";
    echo "<p>Unable to save your application.</p>";
}

// 10. Close database connection
mysqli_stmt_close($stmt);
mysqli_close($conn);

include 'footer.inc';
?>
