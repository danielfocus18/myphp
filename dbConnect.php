<?php
declare(strict_types=1);

/* =========================================================
   FILE: dbConnect.php
   PURPOSE:
   Central database connection and reusable helper functions
   for sessions, security, redirects, and output escaping.
   ========================================================= */

/* ---------------------------------------------------------
   SECTION 1: Start Session
   --------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------
   SECTION 2: Database Configuration
   --------------------------------------------------------- */
$dbHost = 'localhost';
$dbName = 'it3e';
$dbUser = 'root';
$dbPass = '';

/* ---------------------------------------------------------
   SECTION 3: Connect to Database Using PDO
   --------------------------------------------------------- */
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    exit('Database connection failed.');
}

/* ---------------------------------------------------------
   SECTION 4: General Utility Functions
   --------------------------------------------------------- */

/**
 * Safely escape output for HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to another page and stop execution.
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/* ---------------------------------------------------------
   SECTION 5: Flash Message Functions
   --------------------------------------------------------- */

/**
 * Store a success or error message in session.
 */
function setMessage(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear the stored flash message.
 */
function displayMessage(): void
{
    if (!empty($_SESSION['flash'])) {
        $type = e($_SESSION['flash']['type']);
        $message = e($_SESSION['flash']['message']);

        echo "<div class='flash {$type}'>{$message}</div>";

        unset($_SESSION['flash']);
    }
}

/* ---------------------------------------------------------
   SECTION 6: CSRF Protection Functions
   --------------------------------------------------------- */

/**
 * Generate and return CSRF token.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from submitted form.
 */
function verifyCsrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Require valid CSRF token for POST requests.
 */
function requirePostCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf()) {
        setMessage('error', 'Invalid request token. Please try again.');
        redirect($_SERVER['PHP_SELF']);
    }
}

/* ---------------------------------------------------------
   SECTION 7: Authentication Functions
   --------------------------------------------------------- */

/**
 * Check whether a student is logged in.
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['student_id']);
}

/**
 * Protect pages that require login.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setMessage('error', 'Please log in first.');
        redirect('index.php');
    }
}

/* ---------------------------------------------------------
   SECTION 8: Student Utility Functions
   --------------------------------------------------------- */

/**
 * Generate a unique username from first name and surname.
 */
function generateUsername(PDO $pdo, string $fname, string $sname): string
{
    $base = strtolower(
        substr(preg_replace('/\s+/', '', $fname), 0, 3) .
        '.' .
        preg_replace('/\s+/', '', $sname)
    );

    $username = $base;
    $count = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registration WHERE username = ?");

    while (true) {
        $stmt->execute([$username]);

        if ((int)$stmt->fetchColumn() === 0) {
            return $username;
        }

        $count++;
        $username = $base . $count;
    }
}

/**
 * Get a single student by ID.
 */
function getStudentById(PDO $pdo, int $studentId): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, fname, sname, username, emailAdd, slevel
        FROM registration
        WHERE id = ?
    ");

    $stmt->execute([$studentId]);
    $row = $stmt->fetch();

    return $row ?: null;
}