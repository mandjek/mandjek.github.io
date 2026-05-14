<?php
/**
 * 🐔 Ferme Avicole — API PHP
 * 
 * Point d'entrée unique pour toutes les opérations de données.
 * Les données sont stockées dans data/data.json
 */

header('Content-Type: application/json; charset=utf-8');

// --- Configuration ---
define('DATA_DIR', __DIR__ . '/data');
define('DATA_FILE', DATA_DIR . '/data.json');
define('BACKUP_FILE', DATA_DIR . '/data.json.bak');

// Données par défaut (premier lancement)
$DEFAULT_DATA = [
    'achats' => [],
    'depenses' => [],
    'ventes' => [],
    'mortalite' => [],
    'stock' => [
        ['id' => 's1', 'article' => 'Aliment démarrage', 'categorie' => 'Aliment', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 100, 'prixUnitaire' => 0],
        ['id' => 's2', 'article' => 'Aliment croissance', 'categorie' => 'Aliment', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 100, 'prixUnitaire' => 0],
        ['id' => 's3', 'article' => 'Aliment finition', 'categorie' => 'Aliment', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 100, 'prixUnitaire' => 0],
        ['id' => 's4', 'article' => 'Vaccin Newcastle', 'categorie' => 'Médicament', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 5, 'prixUnitaire' => 0],
        ['id' => 's5', 'article' => 'Vaccin Gumboro', 'categorie' => 'Médicament', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 5, 'prixUnitaire' => 0],
        ['id' => 's6', 'article' => 'Vitamines', 'categorie' => 'Médicament', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 10, 'prixUnitaire' => 0],
        ['id' => 's7', 'article' => 'Anticoccidien', 'categorie' => 'Médicament', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 5, 'prixUnitaire' => 0],
        ['id' => 's8', 'article' => 'Antibiotique', 'categorie' => 'Médicament', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 5, 'prixUnitaire' => 0],
        ['id' => 's9', 'article' => 'Désinfectant', 'categorie' => 'Hygiène', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 5, 'prixUnitaire' => 0],
        ['id' => 's10', 'article' => 'Copeaux de bois', 'categorie' => 'Litière', 'stockInitial' => 0, 'entrees' => 0, 'sorties' => 0, 'seuilAlerte' => 20, 'prixUnitaire' => 0],
    ],
    'bandes' => [],
    'categoriesAchats' => ['Poussins', 'Aliment démarrage', 'Aliment croissance', 'Aliment finition', 'Médicaments', 'Vaccins', 'Vitamines', 'Désinfectants', 'Litière (copeaux)', "Matériel d'élevage", 'Équipement'],
    'categoriesDepenses' => ['Électricité', 'Eau', 'Carburant', 'Transport', "Main d'œuvre", 'Salaires', 'Location', 'Entretien bâtiment', 'Réparations', 'Téléphone/Internet', 'Taxes/Impôts', 'Divers'],
    'typesVentes' => ['Poulets', 'Fientes (fumier)', 'Sous-produits', 'Autre'],
    'causesMortalite' => ['Maladie', 'Accident', 'Prédateur', 'Cause inconnue', 'Écrasement', 'Stress thermique'],
    'modesPaiement' => ['Espèces', 'Mobile Money', 'Virement', 'Chèque', 'Crédit']
];

// --- Helpers ---

function readData() {
    global $DEFAULT_DATA;
    
    // Créer le dossier data/ s'il n'existe pas
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    if (!file_exists(DATA_FILE)) {
        writeData($DEFAULT_DATA);
        return $DEFAULT_DATA;
    }
    
    $raw = file_get_contents(DATA_FILE);
    $parsed = json_decode($raw, true);
    
    if (!$parsed || !is_array($parsed)) {
        writeData($DEFAULT_DATA);
        return $DEFAULT_DATA;
    }
    
    // Migration : ajouter les clés manquantes
    $changed = false;
    foreach ($DEFAULT_DATA as $key => $defaultVal) {
        if (!isset($parsed[$key])) {
            $parsed[$key] = $defaultVal;
            $changed = true;
        }
    }
    if ($changed) writeData($parsed);
    
    return $parsed;
}

function writeData($data) {
    // Créer le dossier data/ s'il n'existe pas
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    // Sauvegarde automatique avant écriture
    if (file_exists(DATA_FILE)) {
        copy(DATA_FILE, BACKUP_FILE);
    }
    
    file_put_contents(
        DATA_FILE,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Routage ---

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'read';

// CORS (si accès depuis un autre domaine)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($method === 'OPTIONS') { http_response_code(204); exit; }

switch ($action) {

    // GET api.php?action=read — Lire toutes les données
    case 'read':
        $data = readData();
        jsonResponse(['success' => true, 'data' => $data]);
        break;

    // POST api.php?action=save — Sauvegarder toutes les données
    case 'save':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !is_array($input)) {
            jsonResponse(['success' => false, 'error' => 'Corps de requête invalide'], 400);
        }
        writeData($input);
        jsonResponse(['success' => true, 'message' => 'Données sauvegardées']);
        break;

    // GET api.php?action=export — Télécharger le JSON
    case 'export':
        $data = readData();
        $payload = [
            'version' => 1,
            'exportedAt' => date('c'),
            'data' => $data
        ];
        $dateStr = date('Ymd');
        header('Content-Type: application/json');
        header("Content-Disposition: attachment; filename=\"ferme_avicole_sauvegarde_{$dateStr}.json\"");
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;

    // POST api.php?action=import — Importer un JSON
    case 'import':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            jsonResponse(['success' => false, 'error' => 'JSON invalide'], 400);
        }
        // Accepter le format {data: {...}} ou directement {...}
        $incoming = isset($input['data']) ? $input['data'] : $input;
        if (!isset($incoming['achats']) && !isset($incoming['depenses'])) {
            jsonResponse(['success' => false, 'error' => 'Format de fichier non reconnu'], 400);
        }
        // Compléter avec les clés par défaut
        global $DEFAULT_DATA;
        foreach ($DEFAULT_DATA as $key => $val) {
            if (!isset($incoming[$key])) $incoming[$key] = $val;
        }
        writeData($incoming);
        jsonResponse(['success' => true, 'data' => $incoming, 'message' => 'Données importées']);
        break;

    // POST api.php?action=reset — Réinitialiser
    case 'reset':
        writeData($DEFAULT_DATA);
        jsonResponse(['success' => true, 'data' => $DEFAULT_DATA, 'message' => 'Données réinitialisées']);
        break;

    default:
        jsonResponse(['success' => false, 'error' => "Action inconnue: {$action}"], 400);
}
