<?php  
require_once("inc/init.inc.php");

// Redirection si connecté
if(userConnecte()){
	header('location:profil.php');
}

// Traitement en PHP
if($_POST){
	//debug($_POST, 0);

	/*
	* Vérification sur le pseudo
	*/
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['pseudo']); // preg_match va me retourner FALSE (0) si un caractère non-autorisé est trouvé dans la chaîne de caractères.

	if(!$verif_caractere || strlen(utf8_decode($_POST['pseudo'])) < 3 ||  strlen(utf8_decode($_POST['pseudo'])) > 20){ // S'il y a un mauvais caractère dans le pseudo ou que celui ci ne fait pas la bonne taille.
		$msg .= '<div class="erreur">Le pseudo doit contenir entre 3 et 20 caractères : lettres de A à Z autorisées et chiffres de 0 à 9 !</div>';
	}

	if (empty($msg)) { // Si $msg est vide, cela signifie que tous les champs sont correctement renseignés.

		$resultat = $pdo -> prepare("SELECT * FROM membre WHERE pseudo=:pseudo");
		$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
		$resultat -> execute();

		if($resultat -> rowCount() > 0){
			$msg .= '<div class="erreur">Le pseudo ' . $_POST['pseudo'] . ' est déjà utilisé, merci de choisir un autre pseudo.</div>';
		}
		else{
			$resultat = $pdo -> prepare("INSERT INTO membre (pseudo, mdp, nom, prenom, email, civilite, ville, code_postal, adresse, statut) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, :ville, :code_postal, :adresse, 0)");

			$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
			$_POST['mdp'] = md5($_POST['mdp']);
			$resultat -> bindParam(':mdp', $_POST['mdp'], PDO::PARAM_STR);
			$resultat -> bindParam(':nom', $_POST['nom'], PDO::PARAM_STR);
			$resultat -> bindParam(':prenom', $_POST['prenom'], PDO::PARAM_STR);
			$resultat -> bindParam(':email', $_POST['email'], PDO::PARAM_STR);
			$resultat -> bindParam(':civilite', $_POST['civilite'], PDO::PARAM_STR);
			$resultat -> bindParam(':ville', $_POST['ville'], PDO::PARAM_STR);
			$resultat -> bindParam(':code_postal', $_POST['code_postal'], PDO::PARAM_INT);
			$resultat -> bindParam(':adresse', $_POST['adresse'], PDO::PARAM_STR);

			$resultat -> execute();
			header('location:connexion.php');

		}
	}
}

$page = 'Inscription';
require_once("inc/haut.inc.php");
echo $msg;
?>

<!-- HTML -->
<form method="POST" action="">
	<label>Pseudo</label><br>
	<input type="text" name="pseudo" value="<?php if(isset($_POST['pseudo'])){echo $_POST['pseudo'];} ?>"/><br><br>

	<label>Mot de passe</label><br>
	<input type="password" name="mdp" value="<?php if(isset($_POST['mdp'])){echo $_POST['mdp'];} ?>"/><br><br>

	<label>Nom</label><br>
	<input type="text" name="nom" value="<?php if(isset($_POST['nom'])){echo $_POST['nom'];} ?>"/><br><br>

	<label>Prénom</label><br>
	<input type="text" name="prenom" value="<?php if(isset($_POST['prenom'])){echo $_POST['prenom'];} ?>"/><br><br>

	<label>Email</label><br>
	<input type="email" name="email" value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>"/><br><br>

	<label>Civilité</label><br>
	<select name="civilite">
		<option value="">---</option>
		<option value="m" value="m" <?php if(isset($_POST['civilite']) && $_POST['civilite'] == 'm'){echo 'selected';} ?>>Homme</option>
		<option value="f" value="f" <?php if(isset($_POST['civilite']) && $_POST['civilite'] == 'f'){echo 'selected';} ?>>Femme</option>
	</select><br><br>

	<label>Ville</label><br>
	<input type="text" name="ville" value="<?php if(isset($_POST['ville'])){echo $_POST['ville'];} ?>"/><br><br>

	<label>Code postal</label><br>
	<input type="text" name="code_postal" value="<?php if(isset($_POST['code_postal'])){echo $_POST['code_postal'];} ?>"/><br><br>

	<label>Adresse</label><br>
	<input type="text" name="adresse" value="<?php if(isset($_POST['adresse'])){echo $_POST['adresse'];} ?>"/><br><br>

	<input type="submit" value="Inscription">
</form>

<?php  
require_once("inc/bas.inc.php");
?>