<?php
$page = basename($_SERVER['PHP_SELF']);

// Assurez-vous que la session est démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Générez un nouveau token CSRF si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function afficher_bouton_deconnexion() {
    global $csrf_token;
    echo '<div><button id="logout-btn" class="btn-danger btn col-12" data-csrf="' . htmlspecialchars($csrf_token) . '">Se déconnecter</button></div>';
}

if ($page === 'espace.php' || $page === 'espace_employés.php' || $page === 'espace_admin.php') {
    afficher_bouton_deconnexion();
    echo '
    <div class="footer bg-slate-800 text-white flex justify-between items-center">
        <p>Vous pouvez nous suivre sur les réseaux sociaux tels que :</p>
        <ul>
            <li><a href="https://facebook.com">Facebook</a></li>
            <li><a href="https://twitter.com">Twitter</a></li>
            <li><a href="https://instagram.com">Instagram</a></li>
        </ul>
        <div><p>Copyright 2024 - <span>G</span>ameStore</p></div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/public/JS/count_cart.js"></script>
    <script src="/public/JS/logout.js"></script>';
} else {
    echo '
    <div class="footer bg-slate-800 text-white flex justify-between items-center">
        <p>Vous pouvez nous suivre sur les réseaux sociaux tels que :</p>
        <ul>
            <li><a href="https://facebook.com">Facebook</a></li>
            <li><a href="https://twitter.com">Twitter</a></li>
            <li><a href="https://instagram.com">Instagram</a></li>
        </ul>
        <div><p>Copyright 2024 - <span>G</span>ameStore</p></div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/public/JS/count_cart.js"></script>';
}
?>