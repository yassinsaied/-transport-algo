<?php

require_once 'AlgorithmeRepartition.php';

echo "=== EXEMPLE D'UTILISATION DE L'ALGORITHME ===\n\n";

// Création d'une commande
$commande = new Commande("CMD_DEMO", "Leroy Merlin", 100.0);

// Ajout des tronçons
$troncon1 = new Troncon(
    "T1",
    "Paris",
    "Lyon",
    0.5,
    false
);

$troncon2 = new Troncon(
    "T2",
    "Lyon",
    "Marseille",
    0.5,
    true,
    null,
    "TransporteurX",
    450, // kilométrage
    [
        'forfait' => 30.0,
        'par_km' => 0.12
    ]
);

$commande->ajouterTroncon($troncon1);
$commande->ajouterTroncon($troncon2);

// Exécution de l'algorithme
$algorithme = new AlgorithmeRepartition();
$resultats = $algorithme->calculerRepartition($commande);

// Affichage des résultats
echo "=== RÉSULTATS ===\n";
echo "Succès: " . ($resultats['success'] ? "Oui" : "Non") . "\n";
echo "Message: " . $resultats['message'] . "\n";
echo "Nombre d'ajustements: " . $resultats['iterations'] . "\n\n";

echo "=== DÉTAIL DE LA COMMANDE ===\n";
echo $commande;

echo "\n=== ANALYSE ===\n";
echo "Prix de vente total: " . $commande->getPrixVenteHT() . "€\n";
echo "Somme des prix ventilés: ";
$somme = 0;
foreach ($commande->getTroncons() as $troncon) {
    $somme += $troncon->getPrixVenteVentile();
}
echo $somme . "€\n";

echo "\nRépartition finale:\n";
foreach ($commande->getTroncons() as $troncon) {
    $pourcentageReel = ($troncon->getPrixVenteVentile() / $commande->getPrixVenteHT()) * 100;
    echo "- " . $troncon->getId() . ": " . $troncon->getPrixVenteVentile() . "€ (" . 
         number_format($pourcentageReel, 1) . "%)\n";
}