<?php  
require_once("inc/init.inc.php");

// Redirection si pas connecté
if(!userConnecte()){
	header('location:connexion.php');
}
$page = 'Profil';
require_once("inc/haut.inc.php");

// debug($_SESSION);

echo '<h1>Profil de ' . $_SESSION['membre']['prenom'] . ' ' . $_SESSION['membre']['nom'] . '</h1>';
echo '<p class="centre">Bonjour ' . $_SESSION['membre']['pseudo'] . ' !</p>';
echo '<ul>';
echo '<li> Prénom : <b>' . $_SESSION['membre']['prenom'] . '</b></li>';
echo '<li> Nom : <b>' . $_SESSION['membre']['nom'] . '</b></li>';
echo '<li> Email : <b>' . $_SESSION['membre']['email'] . '</b></li>';
echo '<li> Adresse : <b>' . $_SESSION['membre']['adresse'] . '</b></li>';
echo '<li> Code postal : <b>' . $_SESSION['membre']['code_postal'] . '</b></li>';
echo '<li> Ville : <b>' . $_SESSION['membre']['ville'] . '</b></li>';
echo '<li> Sexe : <b>';
if($_SESSION['membre']['civilite'] == 'm'){
	echo "Homme";
} 
else{
	echo "Femme";
}
echo '</b></li>';
echo '</ul>';



require_once("inc/bas.inc.php");
?>