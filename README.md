# Algorithme de Répartition des Prix de Vente - Transport

## Description

Ce projet implémente un algorithme PHP pour calculer la répartition des prix de vente sur les tronçons d'une commande de transport, en respectant les contraintes de sous-traitance.

### Problématique

Dans le secteur du transport, une commande comprend souvent plusieurs tronçons. Certains peuvent être sous-traités à d'autres transporteurs. Le défi est de répartir le prix de vente total sur chaque tronçon selon une clé de répartition, tout en s'assurant que le montant alloué à un tronçon sous-traité couvre au minimum son prix d'achat.

### Kilométrage

Chaque tronçon peut désormais inclure une information de kilométrage, permettant d'affiner la répartition ou d'ajouter des analyses de coûts au kilomètre.

### Types de frais de sous-traitance

Ajout de la gestion de plusieurs types de frais pour les tronçons sous-traités :

-  Frais fixes (forfaitaires)
-  Frais variables (au kilomètre, au poids, etc.)
-  Possibilité de combiner plusieurs frais pour un même tronçon

---

## Exemple d'utilisation enrichi

```php
$troncon = new Troncon(
    "T1",
    "Paris",
    "Lyon",
    0.5,
    true,
    50.0,
    "TransporteurX",
    450, // kilométrage
    [
        'forfait' => 20.0,
        'par_km' => 0.10
    ]
);
```

---

## Impact sur l'algorithme

-  Le calcul du prix d'achat d'un tronçon sous-traité prend désormais en compte tous les frais définis.
-  Le système reste extensible pour d'autres types de frais (péages, manutention, etc.).

---

## 🏗 Architecture du Projet

```
projet-repartition-transport/
├── Troncon.php              # Classe représentant un tronçon
├── Commande.php             # Classe représentant une commande
├── AlgorithmeRepartition.php # Algorithme principal
├── test.php                 # Suite de tests complète
├── exemple.php              # Exemple d'utilisation simple
└── README.md               # Cette documentation
```

## Installation et Utilisation

### Prérequis

-  PHP 7.4 ou supérieur

### Exécution

1. **Exemple simple** :

```bash
php exemple.php
```

2. **Suite de tests complète** :

```bash
php TestRepartition.php
```

## Classes Principales

### `Troncon`

Représente un segment de transport avec :

-  Origine et destination
-  Clé de répartition (pourcentage)
-  Information de sous-traitance
-  Prix d'achat (si sous-traité)

### `Commande`

Contient :

-  Information client
-  Prix de vente total HT
-  Collection de tronçons

### `AlgorithmeRepartition`

Implémente la logique de calcul :

1. **Validation** : Vérification des contraintes initiales
2. **Répartition initiale** : Application des clés de répartition
3. **Ajustement** : Correction pour respecter les prix d'achat minimum
4. **Vérification finale** : Validation de toutes les contraintes

### Étapes de Calcul

1. **Validation préliminaire**

   -  Vérification que les clés totalisent 100%
   -  Contrôle que le prix total permet de couvrir les coûts

2. **Répartition initiale**

   ```
   Prix ventilé = Prix total × Clé de répartition
   ```

3. **Ajustement itératif**

   -  Identification des tronçons déficitaires
   -  Redistribution proportionnelle du manque
   -  Itération jusqu'à convergence

4. **Vérification finale**
   -  Contraintes de prix d'achat respectées
   -  Conservation du montant total




