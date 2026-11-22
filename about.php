<?php include 'header.inc'; 
require_once 'settings.php';

$sql = "SELECT * FROM about ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>

    <h1>Learn more about the people of Smarter Future</h1>

    <main>
        <h2>About Our Team!</h2>
        <section>
            <h3>Group Information</h3>
            <ul>
                <li><strong>Group name:</strong> IDontKnow</li>
                <li><strong>Class/time:</strong> COS10026-Thursday-Morning class (7AM-10AM)</li>
            </ul>
        </section>
        <section>
            <h3>Group Photo</h3>
            <figure class="groupphoto">
                <img src="images/groupidontknowphoto.webp" alt="Photo of the group members">
                <figcaption>Photo of the members that build this website</figcaption> 
            </figure>
        </section>
        <section>
            <h3>Members Contributions And Quotes</h3>
            <dl>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <dt>
                            <strong><?php echo htmlspecialchars($row['member_name']); ?></strong> - ID:
                            <span class="studentid"><?php echo htmlspecialchars($row['student_id']); ?></span>
                        </dt>
                        <dd class="highlightbox">
                            <p><strong>Part 1:</strong> <?php echo htmlspecialchars($row['part1_role']); ?></p>
                            <p><strong>Part 2:</strong> <?php echo htmlspecialchars($row['part2_role']); ?></p>
                            <p><em><?php echo htmlspecialchars($row['quote_text']); ?></em></p>
                        </dd>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No member data found in database.</p>
                <?php endif; ?>
            </dl>
        </section>
        <section>
            <h3>Fun Facts About Us!</h3>
            <table>
                <caption><strong>Fun facts about us</strong></caption>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Dream job</th>
                        <th>Favourite coding snack</th>
                        <th>Hometown</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['dream_job']); ?></td>
                            <td><?php echo htmlspecialchars($row['snack']); ?></td>
                            <td><?php echo htmlspecialchars($row['hometown']); ?></td>
                        </tr>
                    <?php
                        endwhile;
                    } else {
                        echo '<tr><td colspan="4">No fun facts data found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

<?php include 'footer.inc'; 
if ($conn) {
    mysqli_close($conn);
}
?>