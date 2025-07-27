<?php

require_once 'Commande.php';
require_once 'Troncon.php';

class AlgorithmeRepartition
{
    private const PRECISION = 0.01; // Précision pour les ajustements

    /**
     * Calcule et applique la répartition du prix de vente sur les tronçons
     */
    public function calculerRepartition(Commande $commande): array
    {
        $resultats = [
            'success' => false,
            'message' => '',
            'iterations' => 0,
            'ajustements' => []
        ];

        // Validation préliminaire
        if (!$this->validerCommande($commande, $resultats)) {
            return $resultats;
        }

        // Étape 1: Répartition initiale selon les clés
        $this->repartitionInitiale($commande);

        // Étape 2: Vérification et ajustement des contraintes
        $ajustements = $this->ajusterContraintes($commande);
        $resultats['ajustements'] = $ajustements;
        $resultats['iterations'] = count($ajustements);

        // Vérification finale
        if ($this->verifierContraintes($commande)) {
            $resultats['success'] = true;
            $resultats['message'] = 'Répartition calculée avec succès';
        } else {
            $resultats['success'] = false;
            $resultats['message'] = 'Impossible de respecter toutes les contraintes';
        }

        return $resultats;
    }

    /**
     * Valide la commande avant traitement
     */
    private function validerCommande(Commande $commande, array &$resultats): bool
    {
        if (empty($commande->getTroncons())) {
            $resultats['message'] = 'Aucun tronçon défini';
            return false;
        }

        if (!$commande->validerClesRepartition()) {
            $resultats['message'] = 'La somme des clés de répartition doit égaler 100%';
            return false;
        }

        // Vérifier que la répartition initiale permet de couvrir les coûts
        $coutTotalSousTraitance = 0;
        foreach ($commande->getTroncons() as $troncon) {
            if ($troncon->isEstSousTraite()) {
                $coutTotalSousTraitance += $troncon->getPrixAchatHT();
            }
        }

        if ($coutTotalSousTraitance > $commande->getPrixVenteHT()) {
            $resultats['message'] = 'Le prix de vente total est insuffisant pour couvrir les coûts de sous-traitance';
            return false;
        }

        return true;
    }

    /**
     * Effectue la répartition initiale selon les clés de répartition
     */
    private function repartitionInitiale(Commande $commande): void
    {
        foreach ($commande->getTroncons() as $troncon) {
            $prixVentile = $commande->getPrixVenteHT() * $troncon->getCleRepartition();
            $troncon->setPrixVenteVentile($prixVentile);
        }
    }

    /**
     * Ajuste les prix pour respecter les contraintes de prix d'achat
     */
    private function ajusterContraintes(Commande $commande): array
    {
        $ajustements = [];
        $maxIterations = 10;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $ajustementNecessaire = false;
            $iteration++;

            // Identifier les tronçons qui ne respectent pas les contraintes
            $tronconsAjuster = [];
            $montantDeficit = 0;

            foreach ($commande->getTroncons() as $troncon) {
                if (!$troncon->respecteContraintePrixAchat()) {
                    $deficit = $troncon->getPrixAchatHT() - $troncon->getPrixVenteVentile();
                    $tronconsAjuster[] = [
                        'troncon' => $troncon,
                        'deficit' => $deficit
                    ];
                    $montantDeficit += $deficit;
                    $ajustementNecessaire = true;
                }
            }

            if (!$ajustementNecessaire) {
                break;
            }

            // Ajuster les prix
            $this->redistribuerPrix($commande, $tronconsAjuster, $montantDeficit);

            $ajustements[] = [
                'iteration' => $iteration,
                'deficit_total' => $montantDeficit,
                'troncons_ajustes' => count($tronconsAjuster)
            ];
        }

        return $ajustements;
    }

    /**
     * Redistribue les prix pour couvrir les déficits
     */
    private function redistribuerPrix(Commande $commande, array $tronconsAjuster, float $montantDeficit): void
    {
        // Étape 1: Mettre les tronçons déficitaires au minimum (prix d'achat)
        foreach ($tronconsAjuster as $item) {
            $item['troncon']->setPrixVenteVentile($item['troncon']->getPrixAchatHT());
        }

        // Étape 2: Identifier les tronçons qui peuvent céder du montant
        $tronconsDonateurs = [];
        foreach ($commande->getTroncons() as $troncon) {
            $estDeficitaire = false;
            foreach ($tronconsAjuster as $item) {
                if ($item['troncon'] === $troncon) {
                    $estDeficitaire = true;
                    break;
                }
            }
            
            if (!$estDeficitaire) {
                $tronconsDonateurs[] = $troncon;
            }
        }

        // Étape 3: Redistribuer proportionnellement
        if (!empty($tronconsDonateurs)) {
            $montantTotalDonateurs = 0;
            foreach ($tronconsDonateurs as $troncon) {
                $montantTotalDonateurs += $troncon->getPrixVenteVentile();
            }

            if ($montantTotalDonateurs > $montantDeficit) {
                foreach ($tronconsDonateurs as $troncon) {
                    $proportion = $troncon->getPrixVenteVentile() / $montantTotalDonateurs;
                    $reduction = $montantDeficit * $proportion;
                    $nouveauPrix = $troncon->getPrixVenteVentile() - $reduction;
                    $troncon->setPrixVenteVentile(max(0, $nouveauPrix));
                }
            }
        }
    }

    /**
     * Vérifie que toutes les contraintes sont respectées
     */
    private function verifierContraintes(Commande $commande): bool
    {
        // Vérifier que chaque tronçon respecte sa contrainte
        foreach ($commande->getTroncons() as $troncon) {
            if (!$troncon->respecteContraintePrixAchat()) {
                return false;
            }
        }

        // Vérifier que la somme des prix ventilés égale le prix de vente total
        $sommePrixVentiles = 0;
        foreach ($commande->getTroncons() as $troncon) {
            $sommePrixVentiles += $troncon->getPrixVenteVentile();
        }

        return abs($sommePrixVentiles - $commande->getPrixVenteHT()) < self::PRECISION;
    }
}