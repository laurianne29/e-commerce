<?php  

// ----- Fonction debug()

function debug($var, $mode = 1) {
	echo '<div style="color: white; padding: 5px; background: #' . rand(111111, 999999) . '">';
	$trace = debug_backtrace(); // Retourne des infos (array) sur l'endroit où a été exécuté la fonction.
	$trace = array_shift($trace); // Me retourne la première valeur qui sera également un array.
	echo 'Ce debug a été demandé dans le fichier ' . $trace['file'] . ' à la ligne ' . $trace['line'] . '<hr>';

	if($mode  === 1){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
	else{
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}
	echo '</div>';
}
// ----------------
// L'utilisateur est-il connecté ?
function userConnecte(){ // Cette fonction m'indique si l'utilisateur est connecté. Elle me permettra de gérer les droits d'accessibilité.
	if(isset($_SESSION['membre'])){
		return true;
	}
	else{
		return false;
	}
}
// ----------------
function userConnecteAdmin(){
	// Cette fonction m'indique si l'utilisateur connecté est admin. Elle me permettra de gérer les droits d'accès.

	if (userConnecte() && $_SESSION['membre']['statut'] == 1) {
		return true;
	}
	else{
		return false;
	}
}
// ----------------
function creationPanier(){
	if (!isset($_SESSION['panier'])) {
		$_SESSION['panier'] = array();
		$_SESSION['panier']['titre'] = array();
		$_SESSION['panier']['photo'] = array();
		$_SESSION['panier']['id_produit'] = array();
		$_SESSION['panier']['quantite'] = array();
	}
	// Soit le panier n'existe pas et je le crée (structure vide).
	// Soit le panier existe déjà et retourne TRUE
	return true;
}

// ----------------
function ajouterProduit($titre, $id_produit, $photo, $prix, $quantite){
	// Création du panier
	creationPanier();
	$position = array_search($id_produit, $_SESSION['panier']['id_produit']);
	// Pour vérifier si un produit est déjà dans le panier, je chercher son ID dans $_SESSION['panier']['id_produit'] où sont listés tous les ID. Si array_search() me retourne un chiffre (position), cela signifie que le produit existe dans la panier, sinon si array_search me retourne FALSE, cela signifie que le produit N'EST PAS déjà dans le panier.
	if ($position !== FALSE) {
		$_SESSION['panier']['quantite'][$position] += $quantite;
	}
	else{
		$_SESSION['panier']['quantite'][] = $quantite;
		$_SESSION['panier']['photo'][] = $photo;
		$_SESSION['panier']['prix'][] = $prix;
		$_SESSION['panier']['id_produit'][] = $id_produit;
		$_SESSION['panier']['titre'][] = $titre;
	}
	return true;
}
// ----------------
function montantTotal(){
	$total = 0;
	for($i = 0; $i < count($_SESSION['panier']['prix']); $i++){
		$total += $_SESSION['panier']['prix'][$i] * $_SESSION['panier']['quantite'][$i];
	}
	return round($total, 2);
}
// -----------------
function retirerProduit($id_produit){
	$position = array_search($id_produit, $_SESSION['panier']['id_produit']);
	if($position !== FALSE){
		array_splice($_SESSION['panier']['id_produit'], $position, 1);
		array_splice($_SESSION['panier']['titre'], $position, 1);
		array_splice($_SESSION['panier']['photo'], $position, 1);
		array_splice($_SESSION['panier']['quantite'], $position, 1);
		array_splice($_SESSION['panier']['prix'], $position, 1);
	}
}

?>


