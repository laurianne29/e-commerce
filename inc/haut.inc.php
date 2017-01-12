<!Doctype html>
<html>
    <head>
        <title>Mon Site - <?= $page ?></title>
        <link rel="stylesheet" href= "<?= RACINE_SITE ?>css/style2.css" />
        <link href="https://fonts.googleapis.com/css?family=Dosis|Dancing+Script" rel="stylesheet">
    </head>
    <body>    
        <header>
			<div class="conteneur">                      
				<span>
					<a href="" title="Mon Site">MonSite.com</a>
                </span>    
				<nav>
					<?php
					if(userConnecte()){
						echo '<a href="' . RACINE_SITE . 'profil.php" ';
						if ($page == 'Mon profil') {echo 'class="active"';}
						echo '>Mon profil</a>';
						
						echo '<a href="' . RACINE_SITE . 'boutique.php" ';
						if ($page == 'Boutique') {echo 'class="active"';}
						echo '>Boutique</a>';
						
						echo '<a href="' . RACINE_SITE . 'panier.php" ';
						if ($page == 'Panier') {echo 'class="active"';}
						echo '>Panier</a>';

						echo '<a href="' . RACINE_SITE . 'connexion.php?action=deconnexion">Déconnexion</a>';
					}
					else{
						echo '<a href="' . RACINE_SITE . 'inscription.php" ';
						if ($page == 'Inscription') {echo 'class="active"';}
						echo '>Inscription</a>';
						
						echo '<a href="' . RACINE_SITE . 'connexion.php" ';
						if ($page == 'Connexion') {echo 'class="active"';}
						echo '>Connexion</a>';
						
						echo '<a href="' . RACINE_SITE . 'boutique.php" ';
						if ($page == 'Boutique') {echo 'class="active"';}
						echo '>Boutique</a>';

						echo '<a href="' . RACINE_SITE . 'panier.php" ';
						if ($page == 'Panier') {echo 'class="active"';}
						echo '>Panier</a>';
					}
					// Si je suis admin j'ai un lien vers gestion boutique, gestion membres, gestion commandes.
					if (userConnecteAdmin()) {
						echo '<a href="' . RACINE_SITE . 'admin/gestion_boutique.php" ';
						if ($page == 'Gestion boutique') {echo 'class="active"';}
						echo '>Gestion boutique</a>';
						
						echo '<a href="' . RACINE_SITE . 'admin/gestion_membres.php" ';
						if ($page == 'Gestion membres') {echo 'class="active"';}
						echo '>Gestion membres</a>';

						echo '<a href="' . RACINE_SITE . 'admin/gestion_commandes.php" ';
						if ($page == 'Gestion commandes') {echo 'class="active"';}
						echo '>Gestion commandes</a>';
					}
					?>
				</nav>
			</div>
        </header>
        <section>
			<div class="conteneur">