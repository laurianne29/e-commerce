<?php  
require_once("inc/init.inc.php");

// Traitements !!

// Récupérer et afficher toutes les catégories de produit.

$resultat = $pdo -> query("SELECT DISTINCT categorie FROM produit");


$contenu .= '<div class="boutique-gauche">';
$contenu .= '<ul>';
while ($categorie = $resultat -> fetch(PDO::FETCH_ASSOC)) {
	//debug($categorie);
	$contenu .= '<li><a href="?categorie=' . $categorie['categorie'] . '">' . $categorie['categorie'] . '</a></li>';
}
$contenu .= '</ul>';
$contenu .= '</div>';

// Affiche les produits de la catégorie selectionnée !

if (isset($_GET['categorie']) && $_GET['categorie'] != '') {
	$resultat = $pdo -> prepare("SELECT * FROM produit WHERE categorie = :categorie");
	$resultat -> bindParam(':categorie', $_GET['categorie'], PDO::PARAM_STR);
	$resultat -> execute();

	if(!empty($resultat)){
		$contenu .= '<div class="boutique-droite conteneur">';
		while ($produit = $resultat -> fetch(PDO::FETCH_ASSOC)) {
			//debug($produit);

			$contenu .= '<div class="boutique-produit clearfix">';
			$contenu .= '<h3>' . $produit['titre'] . '</h3>';
			$contenu .= '<p><img src="' . RACINE_SITE . 'photo/' . $produit['photo'] . '" height="80"/></p>';
			$contenu .= '<p>' . substr($produit['description'], 0, 25) . '... </p>';
			$contenu .= '<p><b>' . $produit['prix'] . '€</b></p>';
			$contenu .= '<p style="padding: 5px; background: yellow; margin: 10px;"><a href="fiche_produit.php?id_produit=' . $produit['id_produit'] . '">Voir la fiche</a></p>';
			$contenu .= '</div>';
		}

		$contenu .= '</div>';
	}
}

else{

$resultat = $pdo -> query("SELECT * FROM produit ");

	while ($produit = $resultat -> fetch(PDO::FETCH_ASSOC)) {
		$contenu .= '<div class="boutique-produit clearfix">';
		$contenu .= '<h3>' . $produit['titre'] . '</h3>';
		$contenu .= '<p><img src="' . RACINE_SITE . 'photo/' . $produit['photo'] . '" height="80"/></p>';
		$contenu .= '<p>' . substr($produit['description'], 0, 25) . '... </p>';
		$contenu .= '<p><b>' . $produit['prix'] . '€</b></p>';
		$contenu .= '<p style="padding: 5px; background: yellow; margin: 10px;"><a href="fiche_produit.php?id_produit=' . $produit['id_produit'] . '">Voir la fiche</a></p>';
		$contenu .= '</div>';
	}
	
}

// Si je n'ai pas de catégorie précisé dans l'URL j'aimerai afficher toutes les catégories du site.




$page = 'Boutique';

require_once("inc/haut.inc.php");
echo $contenu;
require_once("inc/bas.inc.php");
?>