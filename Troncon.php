<?php

class Troncon
{
    private string $id;
    private string $origine;
    private string $destination;
    private float $cleRepartition; // Pourcentage de répartition (0.0 à 1.0)
    private bool $estSousTraite;
    private ?float $prixAchatHT;
    private ?string $transporteurSousTraitant;
    private float $prixVenteVentile = 0.0;
    // Ajouts
    private ?int $kilometrage = null;
    private ?array $fraisSousTraitance = null;

    /**
     * @param int|null $kilometrage  Kilométrage du tronçon (optionnel)
     * @param array|null $fraisSousTraitance  Tableau associatif des frais (optionnel)
     */
    public function __construct(
        string $id,
        string $origine,
        string $destination,
        float $cleRepartition,
        bool $estSousTraite = false,
        ?float $prixAchatHT = null,
        ?string $transporteurSousTraitant = null,
        ?int $kilometrage = null,
        ?array $fraisSousTraitance = null
    ) {
        $this->id = $id;
        $this->origine = $origine;
        $this->destination = $destination;
        $this->cleRepartition = $cleRepartition;
        $this->estSousTraite = $estSousTraite;
        $this->prixAchatHT = $prixAchatHT;
        $this->transporteurSousTraitant = $transporteurSousTraitant;
        $this->kilometrage = $kilometrage;
        $this->fraisSousTraitance = $fraisSousTraitance;

        // Validation : si sous-traité, doit avoir un prix d'achat ou des frais
        if ($estSousTraite && $prixAchatHT === null && empty($fraisSousTraitance)) {
            throw new InvalidArgumentException("Un tronçon sous-traité doit avoir un prix d'achat ou des frais de sous-traitance");
        }
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getOrigine(): string { return $this->origine; }
    public function getDestination(): string { return $this->destination; }
    public function getCleRepartition(): float { return $this->cleRepartition; }
    public function isEstSousTraite(): bool { return $this->estSousTraite; }
    public function getPrixAchatHT(): ?float {
        // Si frais de sous-traitance, calculer le total
        if ($this->estSousTraite && is_array($this->fraisSousTraitance)) {
            $total = 0.0;
            if (isset($this->fraisSousTraitance['forfait'])) {
                $total += $this->fraisSousTraitance['forfait'];
            }
            if (isset($this->fraisSousTraitance['par_km']) && $this->kilometrage !== null) {
                $total += $this->fraisSousTraitance['par_km'] * $this->kilometrage;
            }
        
            // Si un prixAchatHT de base est aussi fourni, on l'ajoute
            if ($this->prixAchatHT !== null) {
                $total += $this->prixAchatHT;
            }
            return $total;
        }
        return $this->prixAchatHT;
    }
    public function getTransporteurSousTraitant(): ?string { return $this->transporteurSousTraitant; }
    public function getPrixVenteVentile(): float { return $this->prixVenteVentile; }

    // Setters
    public function setPrixVenteVentile(float $prix): void { 
        $this->prixVenteVentile = $prix; 
    }

    public function setCleRepartition(float $cle): void {
        $this->cleRepartition = $cle;
    }

    /**
     * Vérifie si le prix de vente ventilé respecte la contrainte d'achat
     */
    public function respecteContraintePrixAchat(): bool
    {
        if (!$this->estSousTraite) {
            return true;
        }
        return $this->prixVenteVentile >= $this->prixAchatHT;
    }

    /**
     * Calcule la marge du tronçon
     */
    public function calculerMarge(): float
    {
        if (!$this->estSousTraite) {
            return $this->prixVenteVentile;
        }
        return $this->prixVenteVentile - $this->prixAchatHT;
    }

    public function __toString(): string
    {
        $info = "Tronçon {$this->id}: {$this->origine} → {$this->destination}\n";
        $info .= "  Clé de répartition: " . ($this->cleRepartition * 100) . "%\n";
        $info .= "  Prix de vente ventilé: {$this->prixVenteVentile}€\n";
        if ($this->kilometrage !== null) {
            $info .= "  Kilométrage: {$this->kilometrage} km\n";
        }
        if ($this->estSousTraite) {
            $info .= "  Sous-traité à: {$this->transporteurSousTraitant}\n";
            $info .= "  Prix d'achat HT: " . $this->getPrixAchatHT() . "€\n";
            if (is_array($this->fraisSousTraitance)) {
                foreach ($this->fraisSousTraitance as $type => $valeur) {
                    if ($type === 'par_km') {
                        $info .= "    Frais par km: {$valeur}€/km\n";
                    } else {
                        $info .= "    Frais $type: {$valeur}€\n";
                    }
                }
            }
            $info .= "  Marge: " . $this->calculerMarge() . "€\n";
        }
        return $info;
    }
}