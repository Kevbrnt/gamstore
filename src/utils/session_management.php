<?php
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

require_once __DIR__ . '/../models/connect_bdd.php';

function startUserSession($userId, $rememberMe = false) {
    global $pdo;

    $sessionId = substr(bin2hex(random_bytes(32)), 0, 64);
    $csrfTokenSecret = bin2hex(random_bytes(32));
    $expiry = $rememberMe ? time() + (30 * 24 * 60 * 60) : time() + (2 * 60 * 60);

    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_id, csrf_token_secret, expiry) VALUES (:user_id, :session_id, :csrf_token_secret, :expiry)");
    $stmt->execute([
        ':user_id' => $userId,
        ':session_id' => $sessionId,
        ':csrf_token_secret' => $csrfTokenSecret,
        ':expiry' => date('Y-m-d H:i:s', $expiry)
    ]);

    $cookiePath = '/';

    setcookie('session_id', $sessionId, [
        'expires' => $expiry,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    if ($rememberMe) {
        setcookie('remember_me', $userId, [
            'expires' => $expiry,
            'path' => $cookiePath,
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    $_SESSION['id'] = $userId;
    $_SESSION['csrf_token_secret'] = $csrfTokenSecret;

    return [
        'user_id' => $userId,
        'session_id' => $sessionId,
        'csrf_token_secret' => $csrfTokenSecret,
        'expiry' => date('Y-m-d H:i:s', $expiry)
    ];
}

function getUserSession() {
    global $pdo;

    if (isset($_SESSION['id'])) {
        $stmt = $pdo->prepare("
            SELECT u.*, us.session_id, us.expiry
            FROM users u
            LEFT JOIN user_sessions us ON u.id = us.user_id
            WHERE u.id = :id
            ORDER BY us.expiry DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $_SESSION['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return $user;
        }
    }

    if (isset($_COOKIE['session_id'])) {
        $sessionId = $_COOKIE['session_id'];
        $stmt = $pdo->prepare("
            SELECT u.*, us.session_id, us.expiry
            FROM users u
            JOIN user_sessions us ON u.id = us.user_id
            WHERE us.session_id = :session_id AND us.expiry > NOW()
        ");
        $stmt->execute([':session_id' => $sessionId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['id'] = $user['id'];
            return $user;
        }
    }

    if (isset($_COOKIE['remember_me'])) {
        $userId = $_COOKIE['remember_me'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            startUserSession($user['id'], true);
            return $user;
        }
    }

    return null;
}

function endUserSession() {
    global $pdo;

    if (isset($_COOKIE['session_id'])) {
        $sessionId = $_COOKIE['session_id'];
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE session_id = :session_id");
        $stmt->execute([':session_id' => $sessionId]);

        setcookie('session_id', '', time() - 3600, '/', '', true, true);
    }

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', time() - 3600, '/', '', true, true);
    }

    session_unset();
    session_destroy();
}

function isUserLoggedIn() {
    return getUserSession() !== null;
}

function cleanExpiredSessions() {
    global $pdo;
    $pdo->exec("DELETE FROM user_sessions WHERE expiry < NOW()");
}

function initSession() {
    cleanExpiredSessions();
    error_log("Session ID: " . session_id());
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("Cookies: " . print_r($_COOKIE, true));
}
?>