<?php

require_once 'Troncon.php';

class Commande
{
    private string $id;
    private string $client;
    private float $prixVenteHT;
    private array $troncons = [];

    public function __construct(string $id, string $client, float $prixVenteHT)
    {
        $this->id = $id;
        $this->client = $client;
        $this->prixVenteHT = $prixVenteHT;
    }

    public function ajouterTroncon(Troncon $troncon): void
    {
        $this->troncons[] = $troncon;
    }

    public function getTroncons(): array
    {
        return $this->troncons;
    }

    public function getPrixVenteHT(): float
    {
        return $this->prixVenteHT;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * Valide que la somme des clés de répartition égale 100%
     */
    public function validerClesRepartition(): bool
    {
        $somme = 0;
        foreach ($this->troncons as $troncon) {
            $somme += $troncon->getCleRepartition();
        }
        return abs($somme - 1.0) < 0.0001; // Tolérance pour les erreurs de précision
    }

    /**
     * Calcule la marge totale de la commande
     */
    public function calculerMargeTotal(): float
    {
        $margeTotal = 0;
        foreach ($this->troncons as $troncon) {
            $margeTotal += $troncon->calculerMarge();
        }
        return $margeTotal;
    }

    public function __toString(): string
    {
        $info = "Commande {$this->id} - Client: {$this->client}\n";
        $info .= "Prix de vente HT: {$this->prixVenteHT}€\n";
        $info .= "Nombre de tronçons: " . count($this->troncons) . "\n\n";
        
        foreach ($this->troncons as $i => $troncon) {
            $info .= $troncon . "\n";
        }
        
        $info .= "Marge totale: " . $this->calculerMargeTotal() . "€\n";
        
        return $info;
    }
}