<?php
$page = basename($_SERVER['PHP_SELF']);

function afficher_bouton_deconnexion() {
    // Afficher le bouton de déconnexion uniquement sur certaines pages
}
if ($page === 'espace.php' || $page === 'espace_employés.php' || $page === 'espace_admin.php') {
    afficher_bouton_deconnexion();
     echo '<div><a class="btn-danger btn col-12" href="logout.php">Se déconnecter</a></div>
          
           <div class="footer bg-slate-800 text-white flex justify-between items-center">
    <p>Vous pouvez nous suivre sur les réseaux sociaux tels que :</p>
    <ul>
        <li><a href="https://facebook.com">Facebook</a></li>
        <li><a href="https://twitter.com">Twitter</a></li>
        <li><a href="https://instagram.com">Instagram</a></li>
    </ul>
    <div><p>Copyright 2024 - <span>G</span>ameStore</p></div>
</div></div>


<script src="https://cdn.tailwindcss.com"></script>
<script src="JS/count_cart.js"></script>';}
 else {
    echo '<div class="footer bg-slate-800 text-white flex justify-between items-center">
    <p>Vous pouvez nous suivre sur les réseaux sociaux tels que :</p>
    <ul>
        <li><a href="https://facebook.com">Facebook</a></li>
        <li><a href="https://twitter.com">Twitter</a></li>
        <li><a href="https://instagram.com">Instagram</a></li>
    </ul>
    <div><p>Copyright 2024 - <span>G</span>ameStore</p></div>
</div>


<script src="https://cdn.tailwindcss.com"></script>
<script src="JS/count_cart.js"></script>';
}

?>

