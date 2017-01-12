<?php
require_once("../inc/init.inc.php");

// Redirection si pas admin
if(!userConnecteAdmin()){
	header('location:' . RACINE_SITE . 'connexion.php');
}

// TRAITEMENT POUR SUPPRIMER UN MEMBRE
if(isset($_GET['action']) && $_GET['action'] == 'suppression'){ // Si une action de supprimer un membre est demandée dans l'url : 
	
	$resultat = $pdo -> prepare('DELETE FROM membre WHERE id_membre = :id_membre');
	$resultat -> bindParam(':id_membre', $_GET['id_membre'], PDO::PARAM_INT);
	$resultat -> execute();
	$contenu .= '<div class="validation">Le membre <b>' . $_GET['id_membre'] . '</b> a bien été supprimé ! </div>';
	// $_GET['action'] = 'affichage';	
	header('location:?action=affichage');
}


// TRAITEMENT POUR ENREGISTRER ET MODIFIER UN MEMBRE
if($_POST){
	//debug($_POST);
	//debug($_FILES);
	
	//Si je suis dans le cadre d'un ajout voici la requete : 
	
	// Requete pour enregistrer le nouveau membre dans la BDD. 
	$resultat = $pdo -> prepare("INSERT INTO membre(pseudo, mdp, nom, prenom, email, civilite, ville, code_postal, adresse, statut) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, :ville, :code_postal, :adresse, :statut)");
	
	//STRING : 
	$resultat -> bindParam(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
	$_POST['mdp'] = md5($_POST['mdp']);
	$resultat -> bindParam(':mdp', $_POST['mdp'], PDO::PARAM_STR);
	$resultat -> bindParam(':nom', $_POST['nom'], PDO::PARAM_STR);
	$resultat -> bindParam(':prenom', $_POST['prenom'], PDO::PARAM_STR);
	$resultat -> bindParam(':email', $_POST['email'], PDO::PARAM_STR);
	$resultat -> bindParam(':civilite', $_POST['civilite'], PDO::PARAM_STR);
	$resultat -> bindParam(':ville', $_POST['ville'], PDO::PARAM_STR);
	$resultat -> bindParam(':adresse', $_POST['adresse'], PDO::PARAM_STR);
	//INT : 
	$resultat -> bindParam(':code_postal', $_POST['code_postal'], PDO::PARAM_INT);
	$resultat -> bindParam(':statut', $_POST['statut'], PDO::PARAM_INT);
	
	
	/*//ou sinon si je suis dans le cadre d'une modification voici la requete : 
	$resultat = $pdo -> prepare("INSERT INTO membre (id_membre, pseudo, mdp, nom, prenom, email, civilite, ville, code_postal, adresse, statut) VALUES (:id_membre, :pseudo, :mdp, :nom, :prenom, :email, :civilite, :ville, :code_postal, :adresse, :statut)");*/
	
	$resultat -> execute(); 
	$contenu .= '<div class="validation">Le membre <b>' . $_POST['id_membre'] . '</b> a bien été ajouté ! </div>';
	$_GET['action'] = 'affichage';	
}



// Liens d'action : 
$contenu .= '<br/><a href="?action=affichage">Affichage des membres</a>';
$contenu .= '<br/><a href="?action=ajout">Ajout d\'un membre</a><br/>';

// TRAITEMENT POUR L'AFFICHAGE DE TOUS LES MEMBRES
if(isset($_GET['action']) && $_GET['action'] == 'affichage'){ //Si une action est demandée via l'URL et que cette action c'est l'affichage.
	$resultat = $pdo -> query("SELECT * FROM membre"); 
	
	$contenu .= '<br/><h2>Affichage de tous les membres</h2><br/>';
	$contenu .= '<table border="1">';
	$contenu .= '<tr>';
	for($i=0; $i < $resultat -> columnCount(); $i ++){ // ColumnCount() me retourne le nombre de champs (colonnes) dans notre table. 
		$meta = $resultat -> getColumnMeta($i);
		$contenu .= '<th>' . $meta['name'] . '</th>';
		// On récupère le nom de chaque colonne et on l'inscrit dans une cellule TH. 
	}
	$contenu .= '<th colspan="2">Action</th>';
	$contenu .= '</tr>';
	while($ligne = $resultat -> fetch(PDO::FETCH_ASSOC)){
		$contenu .= '<tr>';
		foreach($ligne as $indice => $valeur){
			$contenu .= '<td>' . $valeur . '</td>';
		}
		$contenu .= '<td id="edit"><a href="?action=modification&id_membre=' . $ligne['id_membre'] .  '"><img src="' . RACINE_SITE . 'img/edit.png" /></a></td>';
		$contenu .= '<td id="delete"><a href="?action=suppression&id_membre=' . $ligne['id_membre'] .  '"><img src="' . RACINE_SITE . 'img/delete.png" /></a></td>';
		$contenu .= '</tr>';
	}
	$contenu .= '</table>';
}

$page = 'Gestion membres';
require_once("../inc/haut.inc.php");
echo $contenu; 

if(isset($_GET['action']) && ($_GET['action'] == 'ajout'|| $_GET['action'] == 'modification')){
// Si une action est demandée dans l'URL et que cette action est soit "modification" SOIT "ajout" alors on va afficher le formulaire. 
	
	//Modification : j'ai un id_membre dans l'URL
	if(isset($_GET['id_membre'])){ // S'il y a un id_membre dans l'url on est dans le cadre d'une modification et on récupère les infos de ce membre pour les insérer dans le formulaire.
		
		$resultat = $pdo -> prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
		$resultat -> bindParam(':id_membre', $_GET['id_membre'], PDO::PARAM_INT);
		$resultat -> execute();
		// Je récupère un array avec toutes les infos du membre à modifier : 
		$membre_actuel = $resultat -> fetch(PDO::FETCH_ASSOC);

	}

		$id_membre = (isset($membre_actuel['id_membre'])) ? $membre_actuel['id_membre'] : '';

		$pseudo = 		(isset($membre_actuel['pseudo'])) ? 		$membre_actuel['pseudo'] : '';
		$mdp = 			(isset($membre_actuel['mdp'])) ? 			$membre_actuel['mdp'] : '';
		$nom =	 		(isset($membre_actuel['nom'])) ? 			$membre_actuel['nom'] : '';
		$prenom =	 	(isset($membre_actuel['prenom'])) ? 		$membre_actuel['prenom'] : '';
		$email = 		(isset($membre_actuel['email'])) ? 			$membre_actuel['email'] : '';
		$civilite = 	(isset($membre_actuel['civilite'])) ? 		$membre_actuel['civilite'] : '';
		$ville = 		(isset($membre_actuel['ville'])) ? 			$membre_actuel['ville'] : '';
		$code_postal = 	(isset($membre_actuel['code_postal'])) ? 	$membre_actuel['code_postal'] : '';
		$adresse = 		(isset($membre_actuel['adresse'])) ? 		$membre_actuel['adresse'] : '';
		$statut = 		(isset($membre_actuel['statut'])) ? 		$membre_actuel['statut'] : '';
		$bouton = 		(isset($membre_actuel)) ? 					'Modifer' : 'Enregistrer';
	
	//Ajout : je n'ai pas d'id_membre dans l'URL
	

?>
<!-- AFFICHAGE D'UN FORMULAIRE POUR ENREGISTRER ET MODIFIER UN MEMBRE-->
<br/><h2>Formulaire d'ajout et de modification</h2><br/>
<form action="" method="post" enctype="multipart/form-data">
	
	<label>Pseudo : </label><br/>
	<input type="text" name="pseudo" value="<?= $pseudo ?>"/><br/><br/>

	<label>Mot de passe : </label><br/>
	<input type="password" name="mdp" required/><br/><br/>
	
	<label>Nom : </label><br/>
	<input type="text" name="nom" value="<?= $nom ?>"/><br/><br/>
	
	<label>Prénom : </label><br/>
	<input type="text" name="prenom" value="<?= $prenom ?>"><br/><br/>
	
	<label>Email : </label><br/>
	<input type="text" name="email" value="<?= $email ?>"/><br/><br/>
	
	<label>Sexe : </label><br/>
	<select name="civilite">
		<option <?php echo ($civilite == 'm') ? 'selected' : ''; ?> value="m">Homme</option>
		<option <?php echo ($civilite == 'f') ? 'selected' : ''; ?> value="f">Femme</option>
		<option <?php echo ($civilite == 'mixte') ? 'selected' : ''; ?> value="mixte">Mixte</option>
	</select><br/><br/>

	<label>Ville : </label><br/>
	<input type="text" name="ville" value="<?= $ville ?>"/><br/><br/>
	
	<label>Code postal : </label><br/>
	<input type="text" name="code_postal" value="<?= $code_postal ?>"/><br/><br/>
	
	<label>Adresse : </label><br/>
	<input type="text" name="adresse" value="<?= $adresse ?>"/><br/><br/>
	
	<label>Statut : </label><br/>
	<select name="statut">
		<option <?php echo ($statut == '0') ? 'selected' : ''; ?> value="0">Membre</option>
		<option <?php echo ($statut == '1') ? 'selected' : ''; ?> value="1">Admin</option>
	</select><br/><br/>
	
	<input type="submit" value="<?= $bouton ?>" name="bouton"/><br/><br/>
</form>

<?php
} // On ferme le IF !!! 
require_once("../inc/bas.inc.php");
?>