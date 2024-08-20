<?php
$currentUser = getUserSession();
?>
<header>
    <div id="navbar" class="bg-slate-800 text-white flex justify-between items-center flex-wrap">
        <div class="logo"><h1>Game<span>S</span>tore</h1></div>
        <div class="menuPrincipal">
            <div class="block md:hidden">
                <label id="nav-toggle" for="toggleNavbar" class="flex gap-1 p-2 px-4 border border-current rounded-lg cursor-pointer select-none text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewbox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                    Menu
                </label>
            </div>
            <input type="checkbox" name="" id="toggleNavbar" class="peer hidden">
            <ul class="md:my-0 h-0 w-full md:w-auto md:h-fit overflow-hidden peer-checked:h-fit absolute md:relative peer-checked:relative
            flex gap-3 items-center md:justify-center md:peer-checked:items-center justify-stretch peer-checked:flex-grow peer-checked:flex-col md:peer-checked:flex-row md:peer-checked:flex-grow-0
            opacity-0 md:opacity-100 peer-checked:opacity-100 transition duration-300 peer-checked:translate-y-2 md:peer-checked:translate-y-0">
                <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/Accueil.php">Accueil</a></li>
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'Administrateur'): ?>
                        <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/espace_admin.php">Espace Admin</a></li>
                    <?php elseif ($currentUser['role'] === 'Employés'): ?>
                        <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/espace_employés.php">Menu Employés</a></li>
                    <?php else: ?>
                        <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/games.php">Tous les jeux</a></li>
                        <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/cart.php">Mon panier <span id="cart-count">0</span></a></li>
                        <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/espace.php">Mon espace</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/games.php">Tous les jeux</a></li>
                    <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/register.php">Inscription</a></li>
                    <li class="py-2 px-4 rounded-lg text-center"><a href="../../src/Pages/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <?php if ($currentUser): ?>
        <div class="nav-role">
            <p class="role-asset">Bienvenue, <span class="name"><?php echo htmlspecialchars($currentUser['username']); ?></span> <br>
                Rôle : <span class="role<?php echo htmlspecialchars($currentUser['role']); ?>"><?php echo htmlspecialchars($currentUser['role']); ?></span></p>
        </div>
    <?php endif; ?>
</header>

<script src="https://cdn.tailwindcss.com"></script>