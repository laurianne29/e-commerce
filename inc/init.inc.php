<?php  


// ------- CONNEXION BDD
$pdo = new PDO('mysql:host=localhost;dbname=site', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));


// ------- SESSION
session_start();

// ------- CHEMIN
define("RACINE_SITE", "/PHP/site/");


// ------- VARIABLES
$msg = '';
$page = '';
$contenu = '';

// ------- AUTRES INCLUSIONS
require_once('fonctions.inc.php');