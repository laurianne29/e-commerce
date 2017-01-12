<?php
require_once("inc/init.inc.php");

// Récupérer les infos du produit et les afficher. 

// 1/ On teste si on reçoit quelque chose dans l'url 
if(isset($_GET['id_produit'])){
	$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
	$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
	$resultat -> execute(); 
	
	if($resultat -> rowCount() < 1 || !is_numeric($_GET['id_produit'])){
		header("location:boutique.php");
	}
	
	$produit = $resultat -> fetch(PDO::FETCH_ASSOC);
	
	$contenu .= '<h3>' . $produit['titre'] . '</h3>';
	$contenu .= '<p><img src="' . RACINE_SITE . 'photo/' . $produit['photo'] . '" height="150"/><br/></p>';
	$contenu .= '<p><u>Description :</u><br/>' . $produit['description']. '</p>';
	$contenu .= '<p>Prix : <b style="font-size: 20px">' . $produit['prix'] . '€</b></p>';
	$contenu .= '<p>Référence : ' . $produit['reference'] . '</p>';
	$contenu .= '<p>Taille : ' . $produit['taille'] . '</p>';
	$contenu .= '<p>Couleur : ' . $produit['couleur'] . '</p>';
	$contenu .= '<p>Public : ' . $produit['public'] . '</p>';	
	
	// Pour ajouter le produit au panier avec la quantité (éventuellement la couleur, la taille) nous faisons un formulaire. 
	
	if($produit['stock'] > 0){
		$contenu .= '<form action="panier.php" method="post">';
		$contenu .= '	<input type="hidden" name="id_produit" value="'. $produit['id_produit'] .'" />';
		$contenu .= '	<label>Quantité : </label><br/>';
		$contenu .= '	<select name="quantite">';
		for($i=1; $i <= $produit['stock'] && $i <= 5; $i++){
			$contenu .= '<option>' . $i . '</option>';
		}
		$contenu .= '	</select><br/><br/>';
		$contenu .= '	<input type="submit" value="Ajouter au panier" name="ajouter"/>';
		$contenu .= '</form>';
	}
	else{
		$contenu .= '<p>Rupture de stock !</p>';
	}
}
else{
	header("location:boutique.php");
}

// REQUÊTE : Sélectionner toutes les infos depuis la table produit à condition que la table produit soit différente de la catégorie du produit dans laquelle je suis.

$contenu .= '<br><hr><br><h2>Suggestion de produits</h2>';

$resultat = $pdo -> query("SELECT * FROM produit WHERE categorie != '$produit[categorie]' LIMIT 0,4");

while ($produit = $resultat -> fetch(PDO::FETCH_ASSOC)) {
			$contenu .= '<div class="boutique-produit clearfix" style="float:left; width:140px;">';
			$contenu .= '<h3>' . $produit['titre'] . '</h3>';
			$contenu .= '<p><img src="' . RACINE_SITE . 'photo/' . $produit['photo'] . '" height="80"/></p>';
			$contenu .= '<p>' . substr($produit['description'], 0, 25) . '... </p>';
			$contenu .= '<p><b>' . $produit['prix'] . '€</b></p>';
			$contenu .= '<p style="padding: 5px; background: yellow; margin: 10px;"><a href="?id_produit=' . $produit['id_produit'] . '">Voir la fiche</a></p>';
			$contenu .= '</div>';
		}

	// debug($categorie);


















// 2/ on fait une requete grâce à ce que l'on a récupéré dans l'URL
// 3/ On récupère un objet innexploitable (1 résultat = pas de boucle)
// 4/ on le rend exploitable
// 5/ Et on affiche les infos du produit















// Suppléments :
// Si on ne reçoit pas d'id dans l'url on va fait une redirection vers boutique
// Si le resultat de notre requête est vide IDEM




$page = 'Boutique';
require_once("inc/haut.inc.php");
echo $contenu;
require_once("inc/bas.inc.php");
?>