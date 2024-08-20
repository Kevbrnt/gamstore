<?php
require_once __DIR__ . '/../utils/session_management.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../utils/csrf_token.php';
require_once __DIR__ . '/../models/connect_bdd.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../build');
$dotenv->load();

initSession();

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Token invalide ou manquant.';
} else {
    // Vérifier si le token est valide et non expiré
    $stmt = $pdo->prepare("SELECT user_id FROM password_reset_tokens WHERE token = :token AND expires > NOW()");
    $stmt->execute([':token' => $token]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $error = 'Le lien de réinitialisation est invalide ou a expiré.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || $password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas ou sont vides.';
    } else {
        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':user_id' => $result['user_id']
        ]);

        // Supprimer le token utilisé
        $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE token = :token");
        $stmt->execute([':token' => $token]);

        $success = 'Votre mot de passe a été réinitialisé avec succès.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/CSS/Gamestore.css">
</head>
<body>
<div class="container3 mt-5">
    <h1 class="mb-4">Réinitialisation du mot de passe</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
        </form>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="mt-3">
            <a href="/login.php" class="btn btn-secondary">Retour à la page de connexion</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
