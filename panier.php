<?php
require_once("inc/init.inc.php");

//INCREMENTATION D'UN PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'ajouter'){ // Si une action d'ajouter une unité est demandée dans l'URL. 
	// Je récupère les infos du produit afin d'en récupérer le stock et de limiter l'ajout au stock disponible.
	$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
	$resultat -> bindParam(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
	$resultat -> execute();
	$produit = $resultat -> fetch(PDO::FETCH_ASSOC);
	
	//Je récupère la position du produit dans mon ARRAY panier, afin d'aller en modifier la quatité.
	$position = array_search($_GET['id_produit'], $_SESSION['panier']['id_produit']);

	if($position !== FALSE){
		// Si $position me retourne un chiffre, et que le stock dispo est supérieur à la quantité demandée + 1 unité supplémentaire alors je peux ajouter l'unité supplémentaire au panier.
		if($produit['stock'] >= $_SESSION['panier']['quantite'][$position] + 1 ){
			$_SESSION['panier']['quantite'][$position]++; 
			header('location:panier.php');
		}
		else{// Sinon le stock n'est pas suffisant pour ajouter une unité, j'affiche un message à l'utilisateur.
			$msg .= '<div class="erreur">le stock du produit ' . $_SESSION['panier']['titre'][$position] . ' est limité ! </div>';
		}
		
	} 
}

// DECREMENTATION D'UN PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'retrancher'){
	// Je récupère la position du produit dans mon panier.
	$position = array_search($_GET['id_produit'], $_SESSION['panier']['id_produit']);
	// Si $position me retourne un chiffre,
	if($position !== FALSE){
		// Si la quantité actuelle dans le panier est strictement supérieur à 1, je décrémente la quantité du produit dans mon panier.
		if($_SESSION['panier']['quantite'][$position] > 1 ){
			$_SESSION['panier']['quantite'][$position]--; 
			header('location:panier.php');
		}
		else{ // Sinon si la quantité du produit dans le panier est égale à 1 (ou moins) alors je retire le produit du panier grâce à ma fonction retirerProduit().
			retirerProduit($_SESSION['panier']['id_produit'][$position]);
			header('location:panier.php');
		}
		
	} 
}

// AJOUTER un produit dans le panier
	// Création du panier s'il n'existe pas encore
	// Vérifier que si le produit est déjà dans le panier, le cas échéant ajouter la nouvelle quantité. 

if(isset($_POST['ajouter'])){ // Si un produit a été ajouté via la ficher_produit // Dans ce cas je dois récypérer toutes les infos du produit (requête).
	$resultat = $pdo -> prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
	$resultat -> bindParam(':id_produit', $_POST['id_produit'], PDO::PARAM_INT); // Je récupère sous forme d'un ARRAY toutes les infos de mon produit à ajouter
	$resultat -> execute(); 
	$produit = $resultat -> fetch(PDO::FETCH_ASSOC);
	
	ajouterProduit($produit['titre'], $produit['id_produit'], $produit['photo'], $produit['prix'], $_POST['quantite']); //id_produit, le prix, la quantité, photo, titre
	// Cette fonction que l'on a créé dans fonction.inc.php nous permet dans un premier temps de créer un panier (dans la session) s'il n'existe pas, puis d'ajouter dans SESSION['panier'], la quantité, le titre, la photo, le prix, l'id_produit. 
}	
	
// RETIRER un produit du panier
if(isset($_GET['action']) && $_GET['action'] == 'supprimer'){ // L'action de retirer un produit du panier est demandé en GET (via l'URL)
	retirerProduit($_GET['id_produit']);
	// La fonction retirerProduit que l'on a créé dans fonction.inc.php, va repérer le produit dans notre ARRAY panier (graçe à array_seach()) et va supprimer toutes les infos du produit (quantité, photo, titre, prix, id_produit) dans notre panier, puis remonter le produit suivant en ré-indexant les indices (array_splice()).
}

// VIDER le panier
if(isset($_GET['action']) && $_GET['action'] == 'vider'){
	unset($_SESSION['panier']);
	// Pour vider le panier il suffit simplement de supprimer la partie panier de ma session grâce a unset().
}


// PAIEMENT des achats
	// Retirer les produits commandés de notre stock
	// supprimer le panier
	// Enregistrer la commande dans nos tables commande et détails_commande.

if(isset($_POST['payer'])){
	// Si l'utilisateur a cliqué sur PAYER je vais devoir répondre à deux problématiques:
	//  1- Le sproduits sont-ils toujours en stock
	//  2- J'enregistre la commande, ses détails et le nouveau stock dans la BDD.

	// Pour chaque produit, je vais vérifier le stock.
	for($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++ ){
		// La boucle FOR parcours chaque produit du panier. Et pour chaque produit, je récupère les infos dans la BDD (requête).
		$resultat = $pdo -> query("SELECT * FROM produit WHERE id_produit =" . $_SESSION['panier']['id_produit'][$i]);
		$produit = $resultat -> fetch(PDO::FETCH_ASSOC);
		
		// debug($produit);

		// Si le stock présent dans la BDD est inférieur à la quantité demandé dans le panier, j'ai un problème... Il se peut qu'un autre utilisateur ait acheté un ou plusieurs produit(s) entre temps.
		if($produit['stock'] < $_SESSION['panier']['quantite'][$i]){
			$msg .= '<div class="erreur">Stock restant : ' . $produit['stock'] . '</div>';
			$msg .= '<div class="erreur">Quantité demandée : ' . $_SESSION['panier']['quantite'][$i] . '</div>';
			
			// Cas de figure 1 : Il y a quand même un peu de stock (>0). Dans ce cas, je propose à l'utilisateur le stock restant.
			if($produit['stock'] > 0){
				$msg .= '<div class="erreur">La quantité du produit ' . $_SESSION['panier']['titre'][$i] . ' n\'est pas suffisante. Votre demande a été modifiée</div>';
				$_SESSION['panier']['quantite'][$i] = $produit['stock'];
			}
			else{ // Cas de figure 2 : Il n'y a plus de stock (=0), dans ce cas je retire le produit du panier et j'affiche un message d'erreur au client.
				$msg .= '<div class="erreur">le produit ' . $_SESSION['panier']['titre'][$i] . ' est en rupture. Il a été retiré de votre panier !</div>';
				retirerProduit($_SESSION['panier']['id_produit'][$i]);
				$i--;
				// Nous sommes dans une boucle FOR qui parcours chaque lignes de mon panier grâce à $i qui avance. Lorsque je supprime une ligne, la ligne du dessous prend sa place. Donc $i continuant à avancer va forcément râter une ligne. Donc je force $i à revenir un cran en arrière, et donc à passer sur la ligne qu'il aurait râté.
			}
			$erreur = true; // Si je suis dans ce IF, cela signifie qu'il y a eu un problème de stock. Peu importe l'issue...
		}
	}
	if(!isset($erreur)){ // Si $erreur N'EXISTE PAS (sous-entendu, il n'y a pas eu de problème de stock), dans ce cas je peux consiférer le paiement validé et passer aux enregistrements dans la BDD
		// Validons le formulaire
		$id_membre = $_SESSION['membre']['id_membre'];
		$montant = montantTotal();
		
		$resultat = $pdo -> exec("INSERT INTO commande (id_membre, montant, date_enregistrement, etat) VALUES ('$id_membre', '$montant', NOW(), 'en cours de traitement')"); 
		
		$id_commande = $pdo -> lastInsertId();
		// lastInsertId() me permet de récupérer l'ID de la dernière requête INSERT ou REPLACE effectué sur ma BDD. Donc il s'agit de l'ID de la commande que je viens d'enregistrer.
		
		// La table details_commande attend un enregistrement par produit acheté. Donc je vais faire ce traitement dans une boucle.
		for($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++){
			$id_produit = $_SESSION['panier']['id_produit'][$i];
			$quantite = $_SESSION['panier']['quantite'][$i];
			$prix = $_SESSION['panier']['prix'][$i];
			
			$resultat = $pdo -> exec("INSERT INTO details_commande (id_commande, id_produit, quantite, prix) VALUES ('$id_commande', '$id_produit', '$quantite', '$prix')");
			
			// Pour chaque produit acheté je vais aller modifier le stock en retirant la quantité achetée.
			$resultat2 = $pdo -> exec("UPDATE produit SET stock = (stock - $quantite) WHERE id_produit = '$id_produit'");
		}
		unset($_SESSION['panier']);
	}
}	

$page = 'Panier';
require_once("inc/haut.inc.php");
echo $msg;

// Afficher le panier

echo '<table border="1" style="border-collapse:collapse" cellpadding="7">';
echo '<tr><td colspan="6">PANIER</td></tr>';
echo '<tr>';
echo '	<th>Photo</th>';
echo '	<th>Titre</th>';
echo '	<th>Prix unitaire</th>';
echo '	<th>Quantité</th>';
echo '	<th>Total</th>';
echo '	<th>Supprimer</th>';
echo '</tr>';

if(empty($_SESSION['panier']['id_produit'])){ // Si le panier est vide on affiche simplement un message // 
	echo '<tr><th colspan="6">Votre panier est vide !</th></tr>';
}
else{// Sinon si le panier n'est pas vide, nous faison une boucle FOR qui va tourner autant de fois que j'ai de produits dans mon panier grâce à la fonction count().
	for($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++){
		// A chaque produit j'affiche une ligne avec image, titre, prix, quantié, total pour le produit, liens pour décrémenter, pour incrémenter et pour supprimer le produit du panier.
		echo '<tr>';
		echo '<td><a href="fiche_produit.php?id_produit=' . $_SESSION['panier']['id_produit'][$i] . '"><img src="'. RACINE_SITE . 'photo/' . $_SESSION['panier']['photo'][$i] .'" height="50px"/></a></td>';
		echo '<td>' . $_SESSION['panier']['titre'][$i] .'</td>';
		echo '<td>' . $_SESSION['panier']['prix'][$i] .'€</td>';
		echo '<td><a href="?action=retrancher&id_produit=' . $_SESSION['panier']['id_produit'][$i] .'"><img src="img/moins.png" width="15px" /></a> <input type="text" style="width:40px" disabled=disabled value="' . $_SESSION['panier']['quantite'][$i] .'"/> <a href="?action=ajouter&id_produit=' . $_SESSION['panier']['id_produit'][$i] .'"><img src="img/plus.png" width="15px" /></a></td>';
		echo '<td>' . ($_SESSION['panier']['quantite'][$i] * $_SESSION['panier']['prix'][$i]) .'€</td>';
		echo '<td><a href="?action=supprimer&id_produit=' . $_SESSION['panier']['id_produit'][$i] . '"><img src="img/delete.png"/></a></td>';
		echo '</tr>';
	}
	echo '<tr>';
	echo '	<td colspan="4">TOTAL</td>';
	echo '	<td colspan="2">' . montantTotal() . '€</td>'; // Je mets une ligne avec le montant total, grâce à ma fonction montantTotal() que nous avons crééé dans fonction.inc.php.
	echo '</tr>';
	
	if(userConnecte()){ // Si l'utilisateur est connecté, je lui propose le lien pour payer via un formulaire pour pouvoir passer des infos en POST au système de paiement.
		echo '<tr>';
		echo '<td colspan="6">';
		echo '<form method="post" action="">';
		echo '<input type="hidden" name="montant" value="'. montantTotal() .'" />';
		echo '<input type="hidden" name="key" value="gdh56DQhjx45D"/>';
		echo '<input type="submit" name="payer" value="Paiement"/>';
		echo '</form>';
		echo '</td>';
		echo '</tr>';
	}
	else{ // S'il n'est pas connecté, alors je lui propose les liens de connexion et d'inscription.
		echo '<tr>';
		echo '<td colspan="6">Veuillez vous <a href="connexion.php?page=panier">connecter</a> ou vous <a href="inscription.php">Inscrire</a> pour valider et payer votre panier.</td>';
		echo '</tr>';
	}
	
	echo '<tr>'; // On propose un lien pour supprimer le panier (action=vider).
	echo '<td colspan="6"><a href="?action=vider">Vider le panier</a></td>';
	echo '</tr>';
}
echo '</table>';




require_once("inc/bas.inc.php");

?>