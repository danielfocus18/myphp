<?php
declare(strict_types=1);

/* =========================================================
   FILE: examsDetails.php
   PURPOSE:
   Allows a logged-in user to add examination details for
   registered students and existing courses.
   ========================================================= */

include 'dbConnect.php';
requireLogin();

/* ---------------------------------------------------------
   SECTION 1: Handle Exam Record Submission
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();

    $ccode = strtoupper(trim($_POST['ccode'] ?? ''));
    $studId = (int)($_POST['studid'] ?? 0);
    $sem = $_POST['sem'] ?? '';
    $yr = trim($_POST['yr'] ?? '');
    $iaMarks = (float)($_POST['iaMarks'] ?? 0);
    $exMarks = (float)($_POST['exMarks'] ?? 0);

    $errors = [];

    if ($ccode === '') {
        $errors[] = 'Course code is required.';
    }

    if ($studId <= 0) {
        $errors[] = 'Valid student ID is required.';
    }

    if (!in_array($sem, ['Semester 1', 'Semester 2'], true)) {
        $errors[] = 'Please select a valid semester.';
    }

    if (!preg_match('/^\d{4}$/', $yr)) {
        $errors[] = 'Please select a valid year.';
    }

    if ($iaMarks < 0 || $iaMarks > 40) {
        $errors[] = 'IA marks must be between 0 and 40.';
    }

    if ($exMarks < 0 || $exMarks > 60) {
        $errors[] = 'Exam marks must be between 0 and 60.';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course WHERE ccode = ?");
    $stmt->execute([$ccode]);
    if ((int)$stmt->fetchColumn() === 0) {
        $errors[] = 'The course code does not exist.';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE id = ?");
    $stmt->execute([$studId]);
    if ((int)$stmt->fetchColumn() === 0) {
        $errors[] = 'The student ID does not exist.';
    }

    if (!empty($errors)) {
        setMessage('error', implode(' ', $errors));
        redirect('examsDetails.php');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO examsDetails (
                ccode,
                studID,
                sem,
                yr,
                iaMarks,
                examMarks
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$ccode, $studId, $sem, $yr, $iaMarks, $exMarks]);

        setMessage('success', 'Exam details added successfully.');
    } catch (PDOException $e) {
        setMessage(
            'error',
            'Could not add exam record. A record for this course, semester, year, and student may already exist.'
        );
    }

    redirect('examsDetails.php');
}

/* ---------------------------------------------------------
   SECTION 2: Fetch Supporting Data
   --------------------------------------------------------- */
$courses = $pdo->query("
    SELECT ccode, cname
    FROM course
    ORDER BY ccode ASC
")->fetchAll();

$students = $pdo->query("
    SELECT id, fname, sname
    FROM registration
    ORDER BY id ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- =====================================================
         FILE HEAD
         ===================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams Details</title>
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <header>
        <h1>Exams Details</h1>
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

        <section class="grid-2">
            <!-- -------------------------------------------------
                 EXAM ENTRY FORM
                 ------------------------------------------------- -->
            <div class="card">
                <h2>Add Exam Record</h2>

                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <label for="ccode">Course Code</label>
                    <select name="ccode" id="ccode" class="inputDesign-1" required>
                        <option value="">Select course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= e($course['ccode']) ?>">
                                <?= e($course['ccode'] . ' - ' . $course['cname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="studid">Student ID</label>
                    <select name="studid" id="studid" class="inputDesign-1" required>
                        <option value="">Select student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= e((string)$student['id']) ?>">
                                <?= e($student['id'] . ' - ' . $student['sname'] . ', ' . $student['fname']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <fieldset class="inputDesign-2">
                        <legend>Select Semester</legend>
                        <label class="inline">
                            <input type="radio" name="sem" value="Semester 1" required>
                            Semester 1
                        </label>
                        <label class="inline">
                            <input type="radio" name="sem" value="Semester 2" required>
                            Semester 2
                        </label>
                    </fieldset>

                    <label for="yr">Year</label>
                    <select name="yr" id="yr" class="inputDesign-1" required>
                        <option value="">Select exam year</option>
                        <option value="2021">2021</option>
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>

                    <label for="iaMarks">IA Marks</label>
                    <input
                        type="number"
                        name="iaMarks"
                        id="iaMarks"
                        class="inputDesign-1"
                        min="0"
                        max="40"
                        step="0.01"
                        required
                    >

                    <label for="exMarks">Exam Marks</label>
                    <input
                        type="number"
                        name="exMarks"
                        id="exMarks"
                        class="inputDesign-1"
                        min="0"
                        max="60"
                        step="0.01"
                        required
                    >

                    <div class="form-actions">
                        <input type="submit" value="Enter">
                        <input type="reset" value="Clear Form">
                    </div>
                </form>
            </div>

            <!-- -------------------------------------------------
                 INFORMATION / NOTES
                 ------------------------------------------------- -->
            <div class="card">
                <h2>Notes</h2>
                <ul class="clean-list">
                    <li>Register the student first.</li>
                    <li>Add the course first before entering exam details.</li>
                    <li>IA marks are out of 40.</li>
                    <li>Exam marks are out of 60.</li>
                </ul>
            </div>
        </section>
    </main>

    <!-- =====================================================
         PAGE FOOTER
         ===================================================== -->
    <footer>Improved Student Records System</footer>

    <!-- =====================================================
         PAGE SCRIPT
         ===================================================== -->
    <script src="checkData.js"></script>
</body>
</html>