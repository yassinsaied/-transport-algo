<?php

require_once 'AlgorithmeRepartition.php';

class TestRepartition
{


    /**
     * Test impossible : multiples sous-traitances trop chères
     */
    private function testCasMultiplesSousTraitancesImpossible(): void
    {
        echo "--- Test 7: Cas impossible (multiples sous-traitances trop chères) ---\n";
        $commande = new Commande("CMD007", "Client Impossible", 100.0);
        $troncon1 = new Troncon("T1", "Origine", "Hub1", 0.40);
        $troncon2 = new Troncon("T2", "Hub1", "Hub2", 0.30, true, 80.0, "Sous-traitant A");
        $troncon3 = new Troncon("T3", "Hub2", "Destination", 0.30, true, 50.0, "Sous-traitant B");
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        $commande->ajouterTroncon($troncon3);
        $resultats = $this->algorithme->calculerRepartition($commande);
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    /**
     * Test impossible : frais de sous-traitance (forfait + par km) trop élevés
     */
    private function testCasFraisKilometrageImpossible(): void
    {
        echo "--- Test 8: Cas impossible (frais de sous-traitance trop élevés) ---\n";
        $commande = new Commande("CMD008", "Client Frais Impossible", 100.0);
        $troncon1 = new Troncon("T1", "Paris", "Lyon", 0.5);
        $troncon2 = new Troncon(
            "T2",
            "Lyon",
            "Marseille",
            0.5,
            true,
            null,
            "TransporteurX",
            200, // 200 km
            [
                'forfait' => 60.0,
                'par_km' => 0.5
            ]
        );
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        $resultats = $this->algorithme->calculerRepartition($commande);
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }
    private AlgorithmeRepartition $algorithme;

    public function __construct()
    {
        $this->algorithme = new AlgorithmeRepartition();
    }

    public function executerTousLesTests(): void
    {
        echo "=== TESTS DE L'ALGORITHME DE RÉPARTITION ===\n\n";
        
        $this->testCasExemple();
        $this->testCasSansContrainte();
        $this->testCasContrainteImpossible();
        $this->testCasMultiplesSousTraitances();
        $this->testCasErreurValidation();
        $this->testCasFraisKilometrage();
        $this->testCasMultiplesSousTraitancesImpossible();
        $this->testCasFraisKilometrageImpossible();
    }

    /**
     * Test avec frais de sous-traitance (forfait + par km)
     */
    private function testCasFraisKilometrage()
    {
        echo "--- Test 6: Frais de sous-traitance (forfait + par km) ---\n";

        $commande = new Commande("CMD006", "Client Frais", 200.0);

        $troncon1 = new Troncon("T1", "Paris", "Lyon", 0.5);
        $troncon2 = new Troncon(
            "T2",
            "Lyon",
            "Marseille",
            0.5,
            true,
            null,
            "TransporteurX",
            100, // 100 km
            [
                'forfait' => 20.0,
                'par_km' => 0.5
            ]
        );

        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);

        $algorithme = new AlgorithmeRepartition();
        $resultats = $algorithme->calculerRepartition($commande);

        // Affichage des résultats
        echo "Statut: " . ($resultats['success'] ? "✓ SUCCÈS" : "✗ ÉCHEC") . "\n";
        echo "Message: " . $resultats['message'] . "\n";
        if ($resultats['iterations'] > 0) {
            echo "Itérations d'ajustement: " . $resultats['iterations'] . "\n";
        }
        echo "\nDétail de la commande:\n";
        echo $commande;
        if ($resultats['success']) {
            echo "✓ Toutes les contraintes sont respectées\n";
        }
        echo str_repeat("-", 50) . "\n";
        // Vérification du calcul attendu
        $attendu = 20.0 + (0.5 * 100);
        echo "Prix d'achat attendu pour T2 : $attendu\n";
        echo "Prix d'achat calculé pour T2 : " . $troncon2->getPrixAchatHT() . "\n";
        echo str_repeat("-", 50) . "\n";
    }

    /**
     * Test du cas d'exemple fourni dans l'énoncé
     */
    private function testCasExemple(): void
    {
        echo "--- Test 1: Cas d'exemple (Astre - Leroy Merlin) ---\n";
        
        $commande = new Commande("CMD001", "Leroy Merlin", 100.0);
        
        // Tronçon 1: Dourges -> La Pommeraye (75%, non sous-traité)
        $troncon1 = new Troncon(
            "T1",
            "Dourges (62)",
            "La Pommeraye (49)",
            0.75
        );
        
        // Tronçon 2: La Pommeraye -> Rezé (25%, sous-traité à Jolival pour 36€)
        $troncon2 = new Troncon(
            "T2",
            "La Pommeraye (49)",
            "Rezé (44)",
            0.25,
            true,
            36.0,
            "Jolival"
        );
        
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        
        $resultats = $this->algorithme->calculerRepartition($commande);
        
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    /**
     * Test sans contrainte de sous-traitance
     */
    private function testCasSansContrainte(): void
    {
        echo "--- Test 2: Cas sans sous-traitance ---\n";
        
        $commande = new Commande("CMD002", "Client ABC", 150.0);
        
        $troncon1 = new Troncon("T1", "Paris", "Lyon", 0.60);
        $troncon2 = new Troncon("T2", "Lyon", "Marseille", 0.40);
        
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        
        $resultats = $this->algorithme->calculerRepartition($commande);
        
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    /**
     * Test avec contrainte impossible à satisfaire
     */
    private function testCasContrainteImpossible(): void
    {
        echo "--- Test 3: Cas avec contrainte impossible ---\n";
        
        $commande = new Commande("CMD003", "Client XYZ", 80.0);
        
        $troncon1 = new Troncon("T1", "A", "B", 0.50);
        // Tronçon sous-traité trop cher par rapport au prix de vente total
        $troncon2 = new Troncon("T2", "B", "C", 0.50, true, 85.0, "Transporteur X");
        
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        
        $resultats = $this->algorithme->calculerRepartition($commande);
        
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    /**
     * Test avec multiples sous-traitances
     */
    private function testCasMultiplesSousTraitances(): void
    {
        echo "--- Test 4: Cas avec multiples sous-traitances ---\n";
        
        $commande = new Commande("CMD004", "Client Multi", 200.0);
        
        $troncon1 = new Troncon("T1", "Origine", "Hub1", 0.40);
        $troncon2 = new Troncon("T2", "Hub1", "Hub2", 0.30, true, 45.0, "Sous-traitant A");
        $troncon3 = new Troncon("T3", "Hub2", "Destination", 0.30, true, 35.0, "Sous-traitant B");
        
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        $commande->ajouterTroncon($troncon3);
        
        $resultats = $this->algorithme->calculerRepartition($commande);
        
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    /**
     * Test d'erreur de validation
     */
    private function testCasErreurValidation(): void
    {
        echo "--- Test 5: Erreur de validation (clés incorrectes) ---\n";
        
        $commande = new Commande("CMD005", "Client Erreur", 100.0);
        
        // Clés qui ne totalisent pas 100%
        $troncon1 = new Troncon("T1", "A", "B", 0.60);
        $troncon2 = new Troncon("T2", "B", "C", 0.30); // Total = 90%
        
        $commande->ajouterTroncon($troncon1);
        $commande->ajouterTroncon($troncon2);
        
        $resultats = $this->algorithme->calculerRepartition($commande);
        
        $this->afficherResultats($commande, $resultats);
        echo "\n";
    }

    private function afficherResultats(Commande $commande, array $resultats): void
    {
        echo "Statut: " . ($resultats['success'] ? "✓ SUCCÈS" : "✗ ÉCHEC") . "\n";
        echo "Message: " . $resultats['message'] . "\n";
        
        if ($resultats['iterations'] > 0) {
            echo "Itérations d'ajustement: " . $resultats['iterations'] . "\n";
        }
        
        echo "\nDétail de la commande:\n";
        echo $commande;
        
        if ($resultats['success']) {
            echo "✓ Toutes les contraintes sont respectées\n";
        }
        
        echo str_repeat("-", 50) . "\n";
    }
}

// Exécution des tests
try {
    $test = new TestRepartition();
    $test->executerTousLesTests();
} catch (Exception $e) {
    echo "Erreur lors de l'exécution des tests: " . $e->getMessage() . "\n";
}