<?php
declare(strict_types=1);

/* =========================================================
   FILE: index.php
   PURPOSE:
   Handles student registration, login, and logout.
   ========================================================= */

include 'dbConnect.php';

/* ---------------------------------------------------------
   SECTION 1: Logout Logic
   --------------------------------------------------------- */
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();

    session_start();
    setMessage('success', 'You have been logged out.');
    redirect('index.php');
}

/* ---------------------------------------------------------
   SECTION 2: Form Processing
   --------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();

    $action = $_POST['action'] ?? '';

    /* -----------------------------------------------------
       SUBSECTION 2A: Student Registration
       ----------------------------------------------------- */
    if ($action === 'register') {
        $fname = trim($_POST['fname'] ?? '');
        $sname = trim($_POST['sname'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $languages = $_POST['lagSpoken'] ?? [];
        $dob = $_POST['dob'] ?? '';
        $level = $_POST['level'] ?? '';
        $email = trim($_POST['emadd'] ?? '');
        $nCourses = (int)($_POST['nCourses'] ?? 0);
        $wgt = $_POST['wgt'] !== '' ? (float)$_POST['wgt'] : null;
        $hgt = $_POST['hgt'] !== '' ? (float)$_POST['hgt'] : null;
        $password = $_POST['pword'] ?? '';

        $errors = [];

        if ($fname === '' || $sname === '') {
            $errors[] = 'First name and surname are required.';
        }

        if (!in_array($gender, ['MALE', 'FEMALE'], true)) {
            $errors[] = 'Please select a valid gender.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (!in_array($level, ['100', '200', '300', '400'], true)) {
            $errors[] = 'Please select a valid level.';
        }

        if ($dob === '') {
            $errors[] = 'Date of birth is required.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($nCourses < 0 || $nCourses > 9) {
            $errors[] = 'Number of courses must be between 0 and 9.';
        }

        if (!empty($errors)) {
            setMessage('error', implode(' ', $errors));
            redirect('index.php');
        }

        $langString = !empty($languages)
            ? implode(', ', array_map('trim', $languages))
            : null;

        $username = generateUsername($pdo, $fname, $sname);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO registration (
                    fname,
                    sname,
                    gender,
                    langSpoken,
                    dob,
                    slevel,
                    emailAdd,
                    numOfCourses,
                    wgt,
                    hgt,
                    username,
                    pword
                )
                VALUES (
                    :fname,
                    :sname,
                    :gender,
                    :langSpoken,
                    :dob,
                    :slevel,
                    :emailAdd,
                    :numOfCourses,
                    :wgt,
                    :hgt,
                    :username,
                    :pword
                )
            ");

            $stmt->execute([
                ':fname' => $fname,
                ':sname' => $sname,
                ':gender' => $gender,
                ':langSpoken' => $langString,
                ':dob' => $dob,
                ':slevel' => $level,
                ':emailAdd' => $email,
                ':numOfCourses' => $nCourses,
                ':wgt' => $wgt,
                ':hgt' => $hgt,
                ':username' => $username,
                ':pword' => $passwordHash,
            ]);

            setMessage(
                'success',
                "Registration successful. Your username is {$username}. You can now log in."
            );
            redirect('index.php');
        } catch (PDOException $e) {
            setMessage('error', 'Registration failed. Email may already exist.');
            redirect('index.php');
        }
    }

    /* -----------------------------------------------------
       SUBSECTION 2B: Student Login
       ----------------------------------------------------- */
    if ($action === 'login') {
        $loginId = trim($_POST['student_id'] ?? '');
        $password = $_POST['login_password'] ?? '';

        if ($loginId === '' || $password === '') {
            setMessage('error', 'Student ID and password are required.');
            redirect('index.php');
        }

        $stmt = $pdo->prepare("
            SELECT id, fname, sname, pword
            FROM registration
            WHERE id = ?
        ");
        $stmt->execute([$loginId]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['pword'])) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['fname'] . ' ' . $student['sname'];

            setMessage('success', 'Login successful.');
            redirect('requestResults.php');
        }

        setMessage('error', 'Invalid login details.');
        redirect('index.php');
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
    <title>Student Registration & Login</title>
    <link rel="stylesheet" href="review.css">
</head>
<body>
    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <header>
        <h1>Student Registration & Login</h1>

        <?php if (isLoggedIn()): ?>
            <p class="user-badge">
                Logged in as
                <strong><?= e($_SESSION['student_name'] ?? 'Student') ?></strong>
                (ID: <?= e((string)($_SESSION['student_id'] ?? '')) ?>)
            </p>
        <?php endif; ?>
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
                 REGISTRATION CARD
                 ------------------------------------------------- -->
            <div class="card">
                <h2 id="greet">Register</h2>
                <p id="TodaysDate" class="subtle"></p>

                <form action="" method="POST" enctype="multipart/form-data" id="registrationForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="register">

                    <label for="fname">First Name</label>
                    <input
                        type="text"
                        name="fname"
                        id="fname"
                        class="inputDesign-1"
                        placeholder="Enter your first name"
                        required
                    >
                    <small id="nm" class="helper-text"></small>

                    <label for="sname">Surname</label>
                    <input
                        type="text"
                        name="sname"
                        id="sname"
                        class="inputDesign-1"
                        placeholder="Enter your surname"
                        required
                    >

                    <fieldset class="inputDesign-2">
                        <legend>Select your Gender</legend>
                        <label class="inline">
                            <input type="radio" name="gender" value="MALE" required> Male
                        </label>
                        <label class="inline">
                            <input type="radio" name="gender" value="FEMALE" required> Female
                        </label>
                    </fieldset>

                    <fieldset class="inputDesign-2">
                        <legend>Select all Languages Spoken</legend>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="English"> English</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="French"> French</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="German"> German</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="Twi"> Twi</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="Ga"> Ga</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="Ewe"> Ewe</label>
                        <label class="inline"><input type="checkbox" name="lagSpoken[]" value="Others"> Others</label>
                    </fieldset>

                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="inputDesign-1" required>
                    <small id="birthdayMessage" class="helper-text"></small>

                    <label for="level">Level</label>
                    <select name="level" id="level" class="inputDesign-1" required>
                        <option value="">Select level</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                    </select>

                    <label for="emadd">E-Mail Address</label>
                    <input
                        type="email"
                        name="emadd"
                        id="emadd"
                        class="inputDesign-1"
                        placeholder="Enter your email"
                        required
                    >

                    <label for="nCourses">Number of Courses</label>
                    <input
                        type="number"
                        name="nCourses"
                        id="nCourses"
                        class="inputDesign-1"
                        min="0"
                        max="9"
                        required
                    >

                    <label for="wgt">Weight (kg)</label>
                    <input
                        type="number"
                        name="wgt"
                        id="wgt"
                        class="inputDesign-1"
                        step="0.1"
                        min="1"
                    >

                    <label for="hgt">Height (m)</label>
                    <input
                        type="number"
                        name="hgt"
                        id="hgt"
                        class="inputDesign-1"
                        step="0.01"
                        min="0.5"
                    >

                    <label for="fColor">Favourite Colour</label>
                    <input type="color" name="fColor" id="fColor" class="inputDesign-1 input-color">

                    <label for="lastSemResult">Upload Last Semester Result</label>
                    <input
                        type="file"
                        name="lastSemResult"
                        id="lastSemResult"
                        class="inputDesign-1"
                        accept=".pdf,.doc,.docx,.txt"
                    >
                    <small class="helper-text">
                        Upload is optional and not stored yet in this version.
                    </small>

                    <label for="pword">Password</label>
                    <input
                        type="password"
                        name="pword"
                        id="pword"
                        class="inputDesign-1"
                        minlength="6"
                        maxlength="50"
                        required
                    >

                    <div class="form-actions">
                        <input type="submit" value="Register">
                        <input type="reset" value="Clear Form">
                    </div>
                </form>
            </div>

            <!-- -------------------------------------------------
                 LOGIN CARD
                 ------------------------------------------------- -->
            <div class="card">
                <h2>Student Login</h2>

                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="login">

                    <label for="student_id">Student ID</label>
                    <input
                        type="number"
                        name="student_id"
                        id="student_id"
                        class="inputDesign-1"
                        required
                    >

                    <label for="login_password">Password</label>
                    <input
                        type="password"
                        name="login_password"
                        id="login_password"
                        class="inputDesign-1"
                        required
                    >

                    <div class="form-actions">
                        <input type="submit" value="Login">

                        <?php if (isLoggedIn()): ?>
                            <a class="btn-link" href="index.php?logout=1">Logout</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="note">
                    <h3>How to use this system</h3>
                    <ol>
                        <li>Register a student account</li>
                        <li>Add courses in the course page</li>
                        <li>Add exam records in the exam details page</li>
                        <li>Log in with your student ID and password</li>
                        <li>Request and view your results</li>
                    </ol>
                </div>
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