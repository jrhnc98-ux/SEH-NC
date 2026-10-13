<?php

declare(strict_types=1);

/**
 * Chargeur des classes du diagramme (spécification Jean Robert-Hénaff).
 * Un fichier par classe, organisé par packages, importé dans l'ordre
 * des dépendances : énumérations, domaine métier, services externes,
 * orchestrateur en dernier puisqu'il s'appuie sur tout le reste.
 */

// Package Énumérations
require_once __DIR__ . '/../classes/Enumerations/StatutExigence.php';
require_once __DIR__ . '/../classes/Enumerations/MotCleAction.php';
require_once __DIR__ . '/../classes/Enumerations/StatutZone.php';

// Package Domaine métier
require_once __DIR__ . '/../classes/DomaineMetier/Membre.php';
require_once __DIR__ . '/../classes/DomaineMetier/ZoneCorrection.php';
require_once __DIR__ . '/../classes/DomaineMetier/ExigenceCorrection.php';
require_once __DIR__ . '/../classes/DomaineMetier/RapportExigences.php';

// Package Services externes
require_once __DIR__ . '/../classes/ServicesExternes/SGBD.php';
require_once __DIR__ . '/../classes/ServicesExternes/AssistantIA.php';
require_once __DIR__ . '/../classes/ServicesExternes/DocumentPDF.php';
require_once __DIR__ . '/../classes/ServicesExternes/DocumentEPUB.php';

// Package Orchestrateur
require_once __DIR__ . '/../classes/Orchestrateur/PanneauCorrection.php';
require_once __DIR__ . '/../classes/Orchestrateur/AtelierProduction.php';

// Point d'entrée : l'orchestrateur de l'Atelier.
$atelier = new AtelierProduction();
