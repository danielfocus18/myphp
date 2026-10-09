<?php
declare(strict_types=1);

/* =========================================================
   FILE: results.php
   PURPOSE:
   Displays the logged-in student's results and calculated GPA.
   ========================================================= */

include 'dbConnect.php';
requireLogin();

/* ---------------------------------------------------------
   SECTION 1: Validate Request
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
    setMessage('error', 'Invalid result request.');
    redirect('requestResults.php');
}

/* ---------------------------------------------------------
   SECTION 2: Helper Functions
   --------------------------------------------------------- */

/**
 * Determine letter grade from total marks.
 */
function findGrade(float $marks): string
{
    if ($marks >= 80) {
        return 'A';
    }
    if ($marks >= 75) {
        return 'B+';
    }
    if ($marks >= 70) {
        return 'B';
    }
    if ($marks >= 65) {
        return 'B-';
    }
    if ($marks >= 60) {
        return 'C+';
    }
    if ($marks >= 55) {
        return 'C';
    }
    if ($marks >= 50) {
        return 'C-';
    }
    if ($marks >= 45) {
        return 'D';
    }

    return 'F';
}

/**
 * Calculate weighted grade points.
 */
function calWGP(float $marks, int $creditHours): float
{
    if ($marks >= 80) {
        $gp = 4.0;
    } elseif ($marks >= 75) {
        $gp = 3.5;
    } elseif ($marks >= 70) {
        $gp = 3.0;
    } elseif ($marks >= 65) {
        $gp = 2.5;
    } elseif ($marks >= 60) {
        $gp = 2.0;
    } elseif ($marks >= 55) {
        $gp = 1.5;
    } elseif ($marks >= 50) {
        $gp = 1.0;
    } elseif ($marks >= 45) {
        $gp = 0.5;
    } else {
        $gp = 0.0;
    }

    return $gp * $creditHours;
}

/* ---------------------------------------------------------
   SECTION 3: Authorize Student Request
   --------------------------------------------------------- */
$requestedStudentId = (int)($_POST['studId'] ?? 0);
$loggedInStudentId = (int)($_SESSION['student_id'] ?? 0);

if ($requestedStudentId !== $loggedInStudentId) {
    setMessage('error', 'You can only view your own results.');
    redirect('requestResults.php');
}

/* ---------------------------------------------------------
   SECTION 4: Fetch Student Data
   --------------------------------------------------------- */
$student = getStudentById($pdo, $requestedStudentId);

$records = [];
$ttCreditHours = 0;
$ttWGP = 0.0;
$sgpa = 0.0;

if ($student) {
    $stmt = $pdo->prepare("
        SELECT
            E.sem,
            E.yr,
            E.ccode,
            C.cname,
            C.chr,
            E.iaMarks,
            E.examMarks,
            (E.iaMarks + E.examMarks) AS ttMarks
        FROM examsDetails E
        INNER JOIN course C
            ON C.ccode = E.ccode
        WHERE E.studID = ?
        ORDER BY E.yr DESC, E.sem ASC, E.ccode ASC
    ");

    $stmt->execute([$requestedStudentId]);
    $records = $stmt->fetchAll();

    foreach ($records as $row) {
        $wgp = calWGP((float)$row['ttMarks'], (int)$row['chr']);
        $ttCreditHours += (int)$row['chr'];
        $ttWGP += $wgp;
    }

    if ($ttCreditHours > 0) {
        $sgpa = round($ttWGP / $ttCreditHours, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- =====================================================
         FILE HEAD
         ===================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Results</title>
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <header>
        <h1>Semester Results</h1>
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

        <section class="card">
            <?php if (!$student): ?>
                <h2>Student not found.</h2>
            <?php else: ?>
                <!-- ---------------------------------------------
                     STUDENT INFORMATION
                     --------------------------------------------- -->
                <h2>Student ID: <?= e((string)$student['id']) ?></h2>
                <h2>Student Name: <?= e($student['sname'] . ', ' . $student['fname']) ?></h2>
                <h3>Username: <?= e($student['username']) ?></h3>
                <h3>Level: <?= e($student['slevel']) ?></h3>

                <?php if (!$records): ?>
                    <p>No exam records found for this student.</p>
                <?php else: ?>
                    <!-- -----------------------------------------
                         RESULTS TABLE
                         ----------------------------------------- -->
                    <table>
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Semester</th>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Credit Hrs</th>
                                <th>IA</th>
                                <th>Exam</th>
                                <th>Total</th>
                                <th>Grade</th>
                                <th>WGP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                                <?php
                                $total = (float)$row['ttMarks'];
                                $grade = findGrade($total);
                                $wgp = calWGP($total, (int)$row['chr']);
                                ?>
                                <tr>
                                    <td><?= e($row['yr']) ?></td>
                                    <td><?= e($row['sem']) ?></td>
                                    <td><?= e($row['ccode']) ?></td>
                                    <td><?= e($row['cname']) ?></td>
                                    <td><?= e((string)$row['chr']) ?></td>
                                    <td><?= e(number_format((float)$row['iaMarks'], 2)) ?></td>
                                    <td><?= e(number_format((float)$row['examMarks'], 2)) ?></td>
                                    <td><?= e(number_format($total, 2)) ?></td>
                                    <td><?= e($grade) ?></td>
                                    <td><?= e(number_format($wgp, 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- -----------------------------------------
                         GPA SUMMARY
                         ----------------------------------------- -->
                    <div class="summary-box">
                        <p><strong>Total Credit Hours:</strong> <?= e((string)$ttCreditHours) ?></p>
                        <p><strong>Total Weighted Grade Points:</strong> <?= e(number_format($ttWGP, 2)) ?></p>
                        <p><strong>Semester GPA:</strong> <?= e(number_format($sgpa, 2)) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

    <!-- =====================================================
         PAGE FOOTER
         ===================================================== -->
    <footer>Improved Student Records System</footer>
</body>
</html>