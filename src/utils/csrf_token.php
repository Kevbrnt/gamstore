<?php
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token'])) {
        error_log("Session CSRF token is empty");
        return false;
    }
    $result = hash_equals($_SESSION['csrf_token'], $token);
    error_log("CSRF Verification - Session Token: " . $_SESSION['csrf_token'] . ", Posted Token: " . $token . ", Result: " . ($result ? 'true' : 'false'));

    // Régénérer le token après vérification pour une protection supplémentaire
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    return $result;
}
?>