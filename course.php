<?php
declare(strict_types=1);

/* =========================================================
   FILE: course.php
   PURPOSE:
   Allows a logged-in user to add and view courses.
   ========================================================= */

include 'dbConnect.php';
requireLogin();

/* ---------------------------------------------------------
   SECTION 1: Handle Course Submission
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();

    $ccode = strtoupper(trim($_POST['ccode'] ?? ''));
    $cname = trim($_POST['cname'] ?? '');
    $chrs = (int)($_POST['chrs'] ?? 0);

    $errors = [];

    if ($ccode === '' || strlen($ccode) > 7) {
        $errors[] = 'Course code is required and must not exceed 7 characters.';
    }

    if ($cname === '') {
        $errors[] = 'Course title is required.';
    }

    if ($chrs < 0 || $chrs > 12) {
        $errors[] = 'Credit hours must be between 0 and 12.';
    }

    if (!empty($errors)) {
        setMessage('error', implode(' ', $errors));
        redirect('course.php');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO course (ccode, cname, chr)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$ccode, $cname, $chrs]);

        setMessage('success', 'Course added successfully.');
    } catch (PDOException $e) {
        setMessage('error', 'Could not add course. The course code may already exist.');
    }

    redirect('course.php');
}

/* ---------------------------------------------------------
   SECTION 2: Fetch Existing Courses
   --------------------------------------------------------- */
$stmt = $pdo->query("
    SELECT ccode, cname, chr
    FROM course
    ORDER BY ccode ASC
");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- =====================================================
         FILE HEAD
         ===================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Register</title>
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <header>
        <h1>Course Register</h1>
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
                 COURSE ENTRY FORM
                 ------------------------------------------------- -->
            <div class="card">
                <h2>Add Course</h2>

                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

                    <label for="ccode">Course Code</label>
                    <input
                        type="text"
                        name="ccode"
                        id="ccode"
                        class="inputDesign-1"
                        placeholder="e.g. ITC1234"
                        maxlength="7"
                        required
                    >

                    <label for="cname">Course Title</label>
                    <input
                        type="text"
                        name="cname"
                        id="cname"
                        class="inputDesign-1"
                        placeholder="Enter course title"
                        required
                    >

                    <label for="chrs">Credit Hours</label>
                    <input
                        type="number"
                        name="chrs"
                        id="chrs"
                        class="inputDesign-1"
                        min="0"
                        max="12"
                        required
                    >

                    <div class="form-actions">
                        <input type="submit" value="Enter">
                        <input type="reset" value="Clear Form">
                    </div>
                </form>
            </div>

            <!-- -------------------------------------------------
                 COURSE LIST
                 ------------------------------------------------- -->
            <div class="card">
                <h2>Available Courses</h2>

                <?php if ($courses): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Credit Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= e($course['ccode']) ?></td>
                                    <td><?= e($course['cname']) ?></td>
                                    <td><?= e((string)$course['chr']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No courses have been added yet.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- =====================================================
         PAGE FOOTER
         ===================================================== -->
    <footer>Improved Student Records System</footer>
</body>
</html>