<?php
declare(strict_types=1);

/* =========================================================
   FILE: requestResults.php
   PURPOSE:
   Allows the logged-in student to request their own results.
   ========================================================= */

include 'dbConnect.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- =====================================================
         FILE HEAD
         ===================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Results</title>
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <header>
        <h1>Request Results</h1>
        <p class="user-badge">
            Logged in as
            <strong><?= e($_SESSION['student_name'] ?? 'Student') ?></strong>
            (ID: <?= e((string)($_SESSION['student_id'] ?? '')) ?>)
        </p>
    </header>

    <!-- =====================================================
         NAVIGATION
         ===================================================== -->
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="examsDetails.php">Exam Details</a></li>
            <li><a href="requestResults.php">Request Results</a></li>
        </ul>
    </nav>

    <!-- =====================================================
         MAIN CONTENT
         ===================================================== -->
    <main class="page-main">
        <?php displayMessage(); ?>

        <section class="centered-card">
            <div class="card narrow">
                <h2>View My Results</h2>

                <form action="results.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <label for="studId">Student ID</label>
                    <input
                        type="number"
                        name="studId"
                        id="studId"
                        class="inputDesign-1"
                        value="<?= e((string)($_SESSION['student_id'] ?? '')) ?>"
                        readonly
                        required
                    >

                    <div class="form-actions">
                        <input type="submit" name="cmdRequest" value="View Results">
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- =====================================================
         PAGE FOOTER
         ===================================================== -->
    <footer>Improved Student Records System</footer>
</body>
</html>