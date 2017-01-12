<?php  
require_once("inc/init.inc.php");

// Déconnexion

if(isset($_GET['action']) && $_GET['action'] == 'deconnexion'){
	unset($_SESSION['membre']);
	// session_destroy(); // En commentaire sinon cela détruit mon panier
	header('location:connexion.php');
}

// Redirection si connecté
if(userConnecte()){
	header('location:profil.php');
}

if ($_POST) {
	//debug($_POST);

	$resultat = $pdo -> prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
	$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
	$resultat -> execute();

	if ($resultat -> rowCount() != 0) { // Si le résultat de la requête est différent de 0 cela signifie que l'utilisateur existe bien dans la base de données. Le pseudo est donc valide.
		$membre = $resultat -> fetch(PDO::FETCH_ASSOC);
		if ($membre['mdp'] == md5($_POST['mdp'])) { // Si le MDP du membre correspond au MDP qu'il m'a envoyé dans le formulaire.
			
			// $_SESSION['membre']['pseudo'] = $membre['pseudo'];
			// $_SESSION['membre']['prenom'] = $membre['prenom'];
			// $_SESSION['membre']['email'] = $membre['email'];
			// Plus simple avec une boucle :
			foreach ($membre as $indice => $valeur) {
				if ($indice != 'mdp') {
					$_SESSION['membre'][$indice] = $valeur;
				}
			}
			// On enregistre dans la superglobale $_SESSION à l'indice 'membre' (tableau multidimensionnel) toutes les infos de notre membre qui se connecte sauf son mdp.
			//debug($_SESSION);

			header('location:profil.php');
		}
		else{
			$msg .= '<div class="erreur">Erreur de mot de passe !</div>';
		}
	}

	else{
		$msg .= '<div class="erreur">Erreur de pseudo !</div>';
	}
}

$page = 'Connexion';
require_once("inc/haut.inc.php");
echo $msg;
?>

<!-- HTML -->
<form method="POST" action="">
	<label>Pseudo</label><br>
	<input type="text" name="pseudo"><br><br>

	<label>Mot de passe</label><br>
	<input type="password" name="mdp"><br><br>

	<input type="submit" value="Connexion">
</form>

<?php
require_once("inc/bas.inc.php");
?>
