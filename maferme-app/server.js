const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;
const DATA_FILE = path.join(__dirname, 'data.json');

// Middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.static(path.join(__dirname, 'public')));

// Données par défaut (premier lancement)
const DEFAULT_DATA = {
  achats: [],
  depenses: [],
  ventes: [],
  mortalite: [],
  stock: [
    { id: 's1', article: 'Aliment démarrage', categorie: 'Aliment', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 100, prixUnitaire: 0 },
    { id: 's2', article: 'Aliment croissance', categorie: 'Aliment', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 100, prixUnitaire: 0 },
    { id: 's3', article: 'Aliment finition', categorie: 'Aliment', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 100, prixUnitaire: 0 },
    { id: 's4', article: 'Vaccin Newcastle', categorie: 'Médicament', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 5, prixUnitaire: 0 },
    { id: 's5', article: 'Vaccin Gumboro', categorie: 'Médicament', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 5, prixUnitaire: 0 },
    { id: 's6', article: 'Vitamines', categorie: 'Médicament', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 10, prixUnitaire: 0 },
    { id: 's7', article: 'Anticoccidien', categorie: 'Médicament', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 5, prixUnitaire: 0 },
    { id: 's8', article: 'Antibiotique', categorie: 'Médicament', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 5, prixUnitaire: 0 },
    { id: 's9', article: 'Désinfectant', categorie: 'Hygiène', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 5, prixUnitaire: 0 },
    { id: 's10', article: 'Copeaux de bois', categorie: 'Litière', stockInitial: 0, entrees: 0, sorties: 0, seuilAlerte: 20, prixUnitaire: 0 },
  ],
  bandes: [],
  categoriesAchats: ['Poussins', 'Aliment démarrage', 'Aliment croissance', 'Aliment finition', 'Médicaments', 'Vaccins', 'Vitamines', 'Désinfectants', 'Litière (copeaux)', 'Matériel d\'élevage', 'Équipement'],
  categoriesDepenses: ['Électricité', 'Eau', 'Carburant', 'Transport', 'Main d\'œuvre', 'Salaires', 'Location', 'Entretien bâtiment', 'Réparations', 'Téléphone/Internet', 'Taxes/Impôts', 'Divers'],
  typesVentes: ['Poulets', 'Fientes (fumier)', 'Sous-produits', 'Autre'],
  causesMortalite: ['Maladie', 'Accident', 'Prédateur', 'Cause inconnue', 'Écrasement', 'Stress thermique'],
  modesPaiement: ['Espèces', 'Mobile Money', 'Virement', 'Chèque', 'Crédit']
};

// --- Helpers ---

function readData() {
  try {
    if (!fs.existsSync(DATA_FILE)) {
      writeData(DEFAULT_DATA);
      return DEFAULT_DATA;
    }
    const raw = fs.readFileSync(DATA_FILE, 'utf-8');
    const parsed = JSON.parse(raw);
    // Migration : ajouter les clés manquantes
    let changed = false;
    for (const key of Object.keys(DEFAULT_DATA)) {
      if (!(key in parsed)) {
        parsed[key] = DEFAULT_DATA[key];
        changed = true;
      }
    }
    if (changed) writeData(parsed);
    return parsed;
  } catch (err) {
    console.error('Erreur lecture data.json :', err.message);
    writeData(DEFAULT_DATA);
    return DEFAULT_DATA;
  }
}

function writeData(data) {
  // Sauvegarde avant écriture (rotation simple)
  try {
    if (fs.existsSync(DATA_FILE)) {
      fs.copyFileSync(DATA_FILE, DATA_FILE + '.bak');
    }
  } catch (_) {}
  fs.writeFileSync(DATA_FILE, JSON.stringify(data, null, 2), 'utf-8');
}

// --- API Routes ---

// GET /api/data — Lire toutes les données
app.get('/api/data', (req, res) => {
  const data = readData();
  res.json({ success: true, data });
});

// PUT /api/data — Remplacer toutes les données (sauvegarde complète)
app.put('/api/data', (req, res) => {
  const data = req.body;
  if (!data || typeof data !== 'object') {
    return res.status(400).json({ success: false, error: 'Corps invalide' });
  }
  writeData(data);
  res.json({ success: true, message: 'Données sauvegardées' });
});

// POST /api/data/reset — Réinitialiser
app.post('/api/data/reset', (req, res) => {
  writeData(DEFAULT_DATA);
  res.json({ success: true, data: DEFAULT_DATA, message: 'Données réinitialisées' });
});

// GET /api/data/export — Télécharger le JSON
app.get('/api/data/export', (req, res) => {
  const data = readData();
  const payload = {
    version: 1,
    exportedAt: new Date().toISOString(),
    data
  };
  const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '');
  res.setHeader('Content-Disposition', `attachment; filename="ferme_avicole_sauvegarde_${dateStr}.json"`);
  res.setHeader('Content-Type', 'application/json');
  res.send(JSON.stringify(payload, null, 2));
});

// POST /api/data/import — Importer un JSON
app.post('/api/data/import', (req, res) => {
  let incoming = req.body;
  if (incoming.data) incoming = incoming.data;
  if (!incoming.achats && !incoming.depenses && !incoming.ventes) {
    return res.status(400).json({ success: false, error: 'Format de fichier invalide' });
  }
  // Fusionner avec les clés par défaut
  for (const key of Object.keys(DEFAULT_DATA)) {
    if (!(key in incoming)) incoming[key] = DEFAULT_DATA[key];
  }
  writeData(incoming);
  res.json({ success: true, data: incoming, message: 'Données importées' });
});

// SPA fallback
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// --- Start ---
app.listen(PORT, () => {
  console.log('');
  console.log('  🐔 Ferme Avicole — Gestion');
  console.log(`  ✅ Serveur démarré sur http://localhost:${PORT}`);
  console.log(`  📁 Données stockées dans ${DATA_FILE}`);
  console.log('');
});
