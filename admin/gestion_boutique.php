<?php
require_once("../inc/init.inc.php");

// Redirection si pas admin
if(!userConnecteAdmin()){
	header('location:' . RACINE_SITE . 'connexion.php');
}

// TRAITEMENT POUR SUPPRIMER UN PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'suppression'){ // Si une action de supprimer un produit est demandée dans l'url : 
	
	// Je dois supprimer la photo correspondant à ce produit, je commence donc par une requête de selection pour récupérer toutes les infos du produit. 
	$resultat = $pdo -> prepare('SELECT * FROM produit WHERE id_produit = :id_produit');
	$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
	$resultat -> execute();
	
	//$resultat est un objet innexploitable en l'état, je dois donc faire un fetch(PDO::FETCH_ASSOC) pour récupérer sous forme d'ARRAY
	$produit_a_supprimer = $resultat -> fetch(PDO::FETCH_ASSOC);
	
	// Pour effectuer la suppression d'un fichier sur le serveur, il faut préciser son emplacement exact.
	$chemin_photo_a_supprimer = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'photo/' . $produit_a_supprimer['photo'];

	unlink($chemin_photo_a_supprimer); // Unlink() est la fonction qui permet de supprimer un fichier de notre serveur.
	
	// Lorsque la photo est supprimé du serveur, il suffit de supprimer le produit de la BDD avec DELETE.
	$resultat = $pdo -> prepare('DELETE FROM produit WHERE id_produit = :id_produit');
	$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
	$resultat -> execute();
	$contenu .= '<div class="validation">Le produit <b>' . $_GET['id_produit'] . '</b> a bien été supprimé ! </div>';
	// $_GET['action'] = 'affichage';	
	header('location:?action=affichage'); // On redirige vers la page avec le paramètre 'affichage' pour éviter de conserver le paramètre 'suppression' dans l'URL car cela peut provoquer des erreurs.
}


// TRAITEMENT POUR ENREGISTRER ET MODIFIER UN PRODUIT
if($_POST){
	// debug($_POST);
	// debug($_FILES);
	
	$photo_bdd = ''; 

	if (isset($_GET['action']) && $_GET['action'] == 'modification') { // Si une action de modifier est demandée dans l'URL alors je vais récupérer le nom de la photo du produit actuel et la mettre dans la variable $photo_bdd qui sera ensuite ré-enregistré telle quelle dans la BDD.
		$photo_bdd = $_POST['photo_actuelle'];
	}
	
	if(!empty($_FILES['photo']['name'])){ // Si une photo a été postée via le formulaire, qu'on soit dans le cadre d'une modification ou d'un ajout, je transforme son nom et mets dans la variable photo_bdd. Et également j'enregistre la photo sur le serveur.
		$photo_bdd = $_POST['reference'] . '_' . $_FILES['photo']['name'];
		
		$photo_dossier = $_SERVER['DOCUMENT_ROOT'] . RACINE_SITE . 'photo/' . $photo_bdd; // Pour enregistrer la photo sur le serveur, je définis le chemin exacte de l'emplacement ou je souhaite l'enregistrer. $_SERVER['DOCUMENT_ROOT'] me retourne la racine du serveur.
		
		copy($_FILES['photo']['tmp_name'], $photo_dossier); // Copie la photo depuis son emplacement temporaire, vers son emplacement définitif. 
	}
	
	// Requête pour enregistrer le nouveau produit dans la BDD.
	// Si une action d'ajout est demandée dans l'URL, la requête ne va pas préciser d'id_produit afin qu'il soit auto-incrémenté.
	if (isset($_GET['action']) && $_GET['action'] == 'ajout'){
	$resultat = $pdo -> prepare("INSERT INTO produit (reference, categorie, titre, description, couleur, taille, public, photo, prix, stock) VALUES (:reference, :categorie, :titre, :description, :couleur, :taille, :public, :photo, :prix, :stock)");
	}
	else{ // Requête pour modifier un produit. Si l'action est de modifier un produit, alors la requête intègre l'id_produit du produit à modifier.
	$resultat = $pdo -> prepare("REPLACE INTO produit (id_produit, reference, categorie, titre, description, couleur, taille, public, photo, prix, stock) VALUES (:id_produit, :reference, :categorie, :titre, :description, :couleur, :taille, :public, :photo, :prix, :stock)");

	$resultat -> bindParam(':id_produit', $_POST['id_produit'], PDO::PARAM_INT);
	}

	//STRING : 
	$resultat -> bindParam(':reference', $_POST['reference'], PDO::PARAM_STR);
	$resultat -> bindParam(':categorie', $_POST['categorie'], PDO::PARAM_STR);
	$resultat -> bindParam(':titre', $_POST['titre'], PDO::PARAM_STR);
	$resultat -> bindParam(':description', $_POST['description'], PDO::PARAM_STR);
	$resultat -> bindParam(':couleur', $_POST['couleur'], PDO::PARAM_STR);
	$resultat -> bindParam(':taille', $_POST['taille'], PDO::PARAM_STR);
	$resultat -> bindParam(':public', $_POST['public'], PDO::PARAM_STR);
	$resultat -> bindParam(':photo', $photo_bdd, PDO::PARAM_STR);
	//INT : 
	$resultat -> bindParam(':prix', $_POST['prix'], PDO::PARAM_INT);
	$resultat -> bindParam(':stock', $_POST['stock'], PDO::PARAM_INT);
	

	$resultat -> execute(); 
	$contenu .= '<div class="validation">Le produit <b>' . $_POST['reference'] . '</b> a bien été modifié ! </div>';
	$_GET['action'] = 'affichage';
}

// Liens d'action : 
$contenu .= '<br/><a href="?action=affichage">Affichage des produits</a>';
$contenu .= '<br/><a href="?action=ajout">Ajout d\'un produit</a><br/>';

// TRAITEMENT POUR L'AFFICHAGE DE TOUS LES PRODUITS
if(isset($_GET['action']) && $_GET['action'] == 'affichage'){ //Si une action est demandée via l'URL et que cette action c'est l'affichage, je récupère toutes les infos de tous les produits et je les affiche dans un tableau.
	$resultat = $pdo -> query("SELECT * FROM produit"); 
	
	$contenu .= '<br/><h2>Affichage de tous les produits</h2><br/>';
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
			if($indice == 'photo'){
				$contenu .= '<td><img src="' . RACINE_SITE . 'photo/' . $valeur . '" height="80"/></td>';
			}
			else{
				$contenu .= '<td>' . $valeur . '</td>';
			}
		}

		// Je crée des liens en face de chaque produit, avec les paramètres action (modification ou suppression) et surtout sans oublier l'id_produit du produit afin de savoir lequel doit être supprimer ou modifier.
		$contenu .= '<td id="edit"><a href="?action=modification&id_produit=' . $ligne['id_produit'] .  '"><img src="' . RACINE_SITE . 'img/edit.png" /></a></td>';
		$contenu .= '<td id="delete"><a href="?action=suppression&id_produit=' . $ligne['id_produit'] .  '"><img src="' . RACINE_SITE . 'img/delete.png" /></a></td>';
		$contenu .= '</tr>';
	}
	$contenu .= '</table>';
}


$page = 'Gestion boutique';
require_once("../inc/haut.inc.php");
echo $contenu; 

if(isset($_GET['action']) && ($_GET['action'] == 'ajout'|| $_GET['action'] == 'modification')){
// Si une action est demandée dans l'URL et que cette action est soit "modification" SOIT "ajout" alors on va afficher le formulaire. 
	
	//Modification : j'ai un id_produit dans l'URL
	if(isset($_GET['id_produit'])){ // S'il y a un id_produit dans l'url on est dans le cadre d'une modification et on récupère les infos de ce produit pour les insérer dans le formulaire.
		
		$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
		$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
		$resultat -> execute();
		// Je récupère un array avec toutes les infos du produit à modifier : 
		$produit_actuel = $resultat -> fetch(PDO::FETCH_ASSOC);

	}

		$id_produit = (isset($produit_actuel['id_produit'])) ? $produit_actuel['id_produit'] : '';

		$reference = 	(isset($produit_actuel['reference'])) ? 	$produit_actuel['reference'] : '';
		$categorie = 	(isset($produit_actuel['categorie'])) ? 	$produit_actuel['categorie'] : '';
		$titre = 		(isset($produit_actuel['titre'])) ? 		$produit_actuel['titre'] : '';
		$description = 	(isset($produit_actuel['description'])) ? 	$produit_actuel['description'] : '';
		$couleur = 		(isset($produit_actuel['couleur'])) ? 		$produit_actuel['couleur'] : '';
		$taille = 		(isset($produit_actuel['taille'])) ? 		$produit_actuel['taille'] : '';
		$public = 		(isset($produit_actuel['public'])) ? 		$produit_actuel['public'] : '';
		$photo = 		(isset($produit_actuel['photo'])) ? 		$produit_actuel['photo'] : '';
		$prix = 		(isset($produit_actuel['prix'])) ? 			$produit_actuel['prix'] : '';
		$stock = 		(isset($produit_actuel['stock'])) ? 		$produit_actuel['stock'] : '';
		$bouton = 		(isset($produit_actuel)) ? 					'Modifer' : 'Enregistrer';
	
	//Ajout : je n'ai pas d'id_produit dans l'URL
	

?>
<!-- AFFICHAGE D'UN FORMULAIRE POUR ENREGISTRER ET MODIFIER UN PRODUIT-->
<br/><h2>Formulaire d'ajout et de modification</h2><br/>
<form action="" method="post" enctype="multipart/form-data">
	
	<input type="hidden" name="id_produit" value="<?= $id_produit ?>" />
	
	<label>Référence : </label><br/>
	<input type="text" name="reference" value="<?= $reference ?>"/><br/><br/>

	<label>Catégorie : </label><br/>
	<input type="text" name="categorie" value="<?= $categorie ?>"/><br/><br/>
	
	<label>Titre : </label><br/>
	<input type="text" name="titre" value="<?= $titre ?>"/><br/><br/>
	
	<label>Description : </label><br/>
	<textarea rows="5" cols="21" name="description"><?= $description ?></textarea><br/><br/>
	
	<label>Couleur : </label><br/>
	<input type="text" name="couleur" value="<?= $couleur ?>"/><br/><br/>
	
	<label>Taille : </label><br/>
	<input type="text" name="taille" value="<?= $taille ?>"/><br/><br/>

	<label>Public : </label><br/>
	<select name="public">
		<option <?php echo ($public == 'm') ? 'selected' : ''; ?> value="m">Homme</option>
		<option <?php echo ($public == 'f') ? 'selected' : ''; ?> value="f">Femme</option>
		<option <?php echo ($public == 'mixte') ? 'selected' : ''; ?> value="mixte">Mixte</option>
	</select><br/><br/>
	
	<label>Photo : </label><br/>
	<?php 
		if (!empty($photo)) {
			echo '<img src="' . RACINE_SITE . 'photo/' . $photo . '" height="120"/>';
		}
	?>
	<input type="file" name="photo"/><br/><br/>
	<input type="hidden" name="photo_actuelle" value="<?= $photo ?>" />
	
	<label>Prix : </label><br/>
	<input type="text" name="prix" value="<?= $prix ?>"/><br/><br/>
	
	<label>Stock : </label><br/>
	<input type="text" name="stock" value="<?= $stock ?>"/><br/><br/>
	
	<input type="submit" value="<?= $bouton ?>" name="bouton" /><br/><br/>
</form>
<?php
} // On ferme le IF !!! 
require_once("../inc/bas.inc.php");
?>