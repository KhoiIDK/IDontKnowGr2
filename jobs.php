<?php
require_once("settings.php");
require_once("header.inc");

$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
if (!$conn) {
    die("<p>Database connection failure: " . htmlspecialchars(mysqli_connect_error()) . "</p>");
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

if ($search !== "") {
    $sql = "SELECT * FROM jobs
            WHERE job_ref_num LIKE ?
               OR title LIKE ?
               OR location LIKE ?
               OR department LIKE ?
               OR job_type LIKE ?
               OR description LIKE ?
               OR responsibilities LIKE ?
               OR requirements LIKE ?
            ORDER BY job_id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmt, "ssssssss", $like, $like, $like, $like, $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM jobs ORDER BY job_id ASC";
    $result = mysqli_query($conn, $sql);
}
?>
<!-- Embedded CSS cho trang jobs theo yêu cầu đề bài -->
<style>
    aside {
        float: right;
        width: 25%;
        margin: 0 0 20px 20px;
        padding: 15px;
        border: 2px solid #0056b3;
        background-color: #f8f9fa;
    }
    main {
        overflow: hidden; /* Clearfix cho float aside */
    }
    .job-search-form {
        margin: 15px 0;
    }
</style>

<h1>Smarter Future Infrastructure Solutions</h1>

<main>
    <!-- Intro Section -->
    <section>
        <h2>Current Job Openings (Smart City Infrastructure)</h2>
        <p>Join our consultancy team in partnering with city councils to develop digital platforms for smart transport, energy monitoring, and urban services management.</p>

        <form method="GET" action="jobs.php" class="job-search-form">
            <label for="search">Search jobs:</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, ref (SCM01), location...">
            <button type="submit">Search</button>
            <?php if ($search !== ""): ?>
                <a href="jobs.php">Reset</a>
            <?php endif; ?>
        </form>
    </section>

    <!-- Aside Section bắt buộc theo yêu cầu (đặt bên phải, 25% width, có margin, padding, border) -->
    <aside>
        <h3>Candidate Notice</h3>
        <p>Smarter Future is committed to creating an inclusive workplace. We strongly encourage applications from Aboriginal and Torres Strait Islander peoples for all open positions.</p>
    </aside>

    <!-- Job Listings -->
    <section>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php $count = 0; ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php if ($count > 0): ?>
                    <hr>
                <?php endif; ?>
                <?php $count++; ?>

                <article>
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Reference Number:</strong> <span style="color: red;" title="Inline CSS example"><?php echo htmlspecialchars($row['job_ref_num']); ?></span></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($row['department']); ?></p>
                    <p><strong>Job Type:</strong> <?php echo htmlspecialchars($row['job_type']); ?></p>
                    <p><strong>Salary:</strong> <?php echo htmlspecialchars($row['salary']); ?></p>
                    <p><strong>Reporting Line:</strong> <?php echo htmlspecialchars($row['reporting_line']); ?></p>

                    <h4>Job Description</h4>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>

                    <h4>Key Responsibilities</h4>
                    <ul>
                        <?php
                        $responsibilities = preg_split('/\r\n|\r|\n/', (string)$row['responsibilities']);
                        foreach ($responsibilities as $line) {
                            $line = trim($line);
                            if ($line !== "") {
                                echo "<li>" . htmlspecialchars($line) . "</li>\n";
                            }
                        }
                        ?>
                    </ul>

                    <h4>Requirements</h4>
                    <ul>
                        <?php
                        $requirements = preg_split('/\r\n|\r|\n/', (string)$row['requirements']);
                        foreach ($requirements as $line) {
                            $line = trim($line);
                            if ($line !== "") {
                                echo "<li>" . htmlspecialchars($line) . "</li>\n";
                            }
                        }
                        ?>
                    </ul>

                    <p><a href="apply.php?job=<?php echo urlencode($row['job_ref_num']); ?>">Apply for this position</a></p>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No jobs found matching your search.</p>
        <?php endif; ?>
    </section>
</main>
<?php
if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
require_once("footer.inc");
?>