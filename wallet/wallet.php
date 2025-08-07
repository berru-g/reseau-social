<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "/pages/login.php");
    exit;
}

$userId = intval($_SESSION['user_id']);
$user = getUserById($userId);
if (!$user)
    die("Utilisateur non trouvé");

require_once '../includes/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600&display=swap');
    * {
        border: 1px dashed #9f7bff;
    }
    .wallet {
        max-width: 300px;
        margin: 0 auto;
        padding: 20px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 16px;
        box-shadow: 0 0 30px rgba(0, 255, 231, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 255, 229, 0.605);
    }


    .form-wallet {
        display: grid;
        margin: 0 auto;
        gap: 12px;
        background: #F4F4F4;
        padding: 20px;
        border-radius: 12px;
        box-shadow: inset 0 0 10px rgba(0, 255, 231, 0.05);
        margin-bottom: 30px;
        max-width: 300px;
    }

    .form-wallet input[type="text"],
    input[type="number"] {
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: #F1F1F1;
        color: #444;
        font-size: 1em;
        outline: none;
        transition: 0.2s ease;
    }

    input:focus {
        box-shadow: 0 0 0 2px #00ffe7;
    }

    .btn-wallet {
        padding: 12px;
        border: none;
        border-radius: 8px;
        background: linear-gradient(145deg, #00ffe7, #9f7bff);
        color: #F1F1F1;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-wallet:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 255, 231, 0.3);
    }

    #autocomplete-list {
        background: grey;
        border-radius: 8px;
        margin-top: -8px;
        overflow: hidden;
    }

    #autocomplete-list li {
        padding: 10px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    #autocomplete-list li:hover {
        background: #2a2f376d;
    }

    #wallet-list {
        margin-top: 30px;
    }

    .crypto-item {
        display: flex;
        justify-content: space-between;
        background: #5995e9ff;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 12px;
        align-items: center;
        transition: 0.2s ease;
    }

    .crypto-item:hover {
        box-shadow: 0 0 10px rgba(0, 255, 231, 0.05);
    }

    .crypto-name {
        font-weight: 600;
        color: #555;
    }

    .crypto-meta {
        font-size: 0.9em;
        color: #aaa;
        margin-top: 5px;
    }

    .crypto-actions btn-wallet {
        margin-left: 10px;
        background: none;
        color: #ff7070;
        border: 1px solid #ff7070;
    }

    .crypto-actions btn-wallet:hover {
        background: #ff7070;
        color: #0d1117;
    }

    /* Style de base */
    #personal-wallet {
        margin-top: 40px;
        padding: 20px;
        background: #f5f5f5;
        border-radius: 10px;
    }

    .wallet-controls {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    #crypto-results {
        list-style: none;
        padding: 0;
        margin: 5px 0 0 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 200px;
        overflow-y: auto;
    }

    #crypto-results li {
        padding: 8px 12px;
        cursor: pointer;
    }

    #crypto-results li:hover {
        background-color: #f0f0f0;
    }

    .wallet-summary {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .holdings-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .holding {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        background: white;
        border-radius: 8px;
    }

    .holding-info {
        flex-grow: 1;
    }

    .delete-btn {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #ff4444;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .wallet-controls {
            grid-template-columns: 1fr;
        }
    }
</style>


<body>
    <section id="crypto-comparison">
        <h2>Comparaison des Narratifs Crypto</h2>
        <p>Visualiser l'évolution des narratifs majeurs</p>
        <select id="timeRange">
            <option value="7">7 Jours</option>
            <option value="30">1 Mois</option>
            <option value="365">1 An</option>
        </select>
        <div class="chart-container">
            <canvas id="cryptoChart"></canvas>
        </div>
    </section>

    <section id="personal-wallet">
        <h2>Mon Portefeuille Personnel</h2>
        <div class="wallet-controls">
            <div class="form-group">
                <label>Crypto :</label>
                <input type="text" id="crypto-search" placeholder="Rechercher...">
                <ul id="crypto-results"></ul>
                <input type="hidden" id="selected-crypto-id">
            </div>
            <div class="form-group">
                <label>Prix d'achat ($) :</label>
                <input type="number" id="purchase-price" step="0.000001">
            </div>
            <div class="form-group">
                <label>Quantité :</label>
                <input type="number" id="crypto-quantity" step="0.000001">
            </div>
            <button id="add-to-wallet" class="btn-primary">Ajouter au portefeuille</button>
        </div>

        <div class="wallet-summary">
            <h3>Résumé</h3>
            <p>Total investi : <span id="total-invested">$0.00</span></p>
            <p>Valeur actuelle : <span id="current-value">$0.00</span></p>
            <p>Performance : <span id="performance">0.00%</span></p>
        </div>

        <div id="wallet-holdings" class="holdings-container"></div>
    </section>

    <script>
        // Déclaration debug de userId
        const userId = <?= json_encode($_SESSION['user_id'] ?? 0) ?>;

        // Vérification de la connexion
        if (!userId || userId <= 0) {
            window.location.href = '/login.php';
        }
    </script>

    <!-- Chart 
     <script src="<?= BASE_URL ?>/assets/js/wallet.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.3.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script>
        // Configuration
        const COINGECKO_API = 'https://api.coingecko.com/api/v3';
        const SERVER_API = 'api_wallet.php'; 

        // Éléments DOM
        const timeRange = document.getElementById('timeRange');
        const ctx = document.getElementById('cryptoChart').getContext('2d');
        const cryptoSearch = document.getElementById('crypto-search');
        const cryptoResults = document.getElementById('crypto-results');
        const selectedCryptoId = document.getElementById('selected-crypto-id');
        const addToWalletBtn = document.getElementById('add-to-wallet');
        const walletHoldings = document.getElementById('wallet-holdings');

        // Narratifs crypto (votre configuration existante)
        const narratives = {
            bitcoin: "Réserve de valeur numérique (PoW)",
            ethereum: "Blockchain de smart contracts",
            solana: "Blockchain de smart contracts scalable (Layer 1)",
            aave: "DeFi (Prêts et emprunts décentralisés)",
            "the-graph": "IA et Big Data",
            centrifuge: "RWA (Tokenisation d'actifs réels)",
            polkadot: "Interopérabilité",
            monero: "Confidentialité",
            "axie-infinity": "Gaming (Play-to-Earn)",
            chainlink: "Infrastructure Blockchain (Oracles)",
            makerdao: "Stablecoins (DAI)",
            helium: "Réseaux IoT décentralisés"
        };

        // 1. Système de comparaison (votre code existant amélioré)
        let comparisonChart;

        async function initComparisonChart() {
            const days = timeRange.value;
            const data = await fetchCryptoData(days);

            comparisonChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: data.map((coin, index) => ({
                        label: `${narratives[coin.id]} (${coin.symbol.toUpperCase()})`,
                        data: coin.sparkline_in_7d.price.map((price, i, arr) => ({
                            x: new Date(Date.now() - (arr.length - 1 - i) * 86400000),
                            y: ((price - arr[0]) / arr[0]) * 100
                        })),
                        borderColor: getColorForCrypto(coin.id),
                        fill: false,
                        tension: 0.1
                    }))
                },
                options: {
                    responsive: true,
                    plugins: {
                        zoom: {
                            pan: { enabled: true, mode: 'x' },
                            zoom: { enabled: true, mode: 'x' }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label || '';
                                    const value = context.parsed.y.toFixed(2);
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { type: 'time', time: { unit: 'day' } },
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: 'Variation (%)' }
                        }
                    }
                }
            });
        }

        // 2. Système de wallet
        async function loadWallet() {
            try {
                const response = await fetch(`${SERVER_API}?action=get&user_id=${userId}`);
                const holdings = await response.json();

                if (!holdings.length) {
                    walletHoldings.innerHTML = '<p>Aucune crypto dans votre portefeuille</p>';
                    return;
                }

                // Récupération des prix actuels
                const ids = holdings.map(h => h.crypto_id).join(',');
                const pricesRes = await fetch(`${COINGECKO_API}/simple/price?ids=${ids}&vs_currencies=usd&include_24hr_change=true`);
                const prices = await pricesRes.json();

                // Calcul des totaux
                let totalInvested = 0;
                let totalCurrent = 0;

                // Affichage des holdings
                walletHoldings.innerHTML = holdings.map(holding => {
                    const currentPrice = prices[holding.crypto_id]?.usd || 0;
                    const currentValue = currentPrice * holding.quantity;
                    const investedValue = holding.purchase_price * holding.quantity;
                    const profit = currentValue - investedValue;
                    const profitPercentage = (profit / investedValue) * 100;

                    totalInvested += investedValue;
                    totalCurrent += currentValue;

                    return `
                <div class="holding">
                    <img src="https://cryptoicon-api.vercel.app/api/icon/${holding.crypto_id.toLowerCase()}" alt="${holding.crypto_name}" width="24">
                    <div class="holding-info">
                        <h4>${holding.crypto_name}</h4>
                        <p>${holding.quantity} @ $${holding.purchase_price.toFixed(6)}</p>
                    </div>
                    <div class="holding-value" style="color: ${profit >= 0 ? '#4CAF50' : '#F44336'}">
                        $${currentValue.toFixed(2)} (${profitPercentage.toFixed(2)}%)
                    </div>
                    <button class="delete-btn" data-id="${holding.crypto_id}">×</button>
                </div>
            `;
                }).join('');

                // Mise à jour du résumé
                document.getElementById('total-invested').textContent = `$${totalInvested.toFixed(2)}`;
                document.getElementById('current-value').textContent = `$${totalCurrent.toFixed(2)}`;
                document.getElementById('performance').textContent = `${((totalCurrent - totalInvested) / totalInvested * 100).toFixed(2)}%`;

                // Mise à jour du graphique de comparaison avec les cryptos du wallet
                updateComparisonChartWithWallet(holdings);

            } catch (error) {
                console.error('Erreur:', error);
                walletHoldings.innerHTML = '<p>Erreur de chargement du portefeuille</p>';
            }
        }

        // 3. Autocomplétion et gestion du formulaire
        cryptoSearch.addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                cryptoResults.innerHTML = '';
                return;
            }

            try {
                const response = await fetch(`${COINGECKO_API}/search?query=${query}`);
                const data = await response.json();
                displaySearchResults(data.coins.slice(0, 5));
            } catch (error) {
                console.error('Erreur:', error);
            }
        });

        function displaySearchResults(coins) {
            cryptoResults.innerHTML = coins.map(coin => `
        <li data-id="${coin.id}">
            <img src="${coin.thumb}" alt="${coin.name}" width="20">
            ${coin.name} (${coin.symbol.toUpperCase()})
        </li>
    `).join('');

            cryptoResults.querySelectorAll('li').forEach(item => {
                item.addEventListener('click', () => {
                    selectedCryptoId.value = item.getAttribute('data-id');
                    cryptoSearch.value = item.textContent.trim();
                    cryptoResults.innerHTML = '';
                });
            });
        }

        addToWalletBtn.addEventListener('click', async () => {
            const cryptoId = selectedCryptoId.value;
            const cryptoName = cryptoSearch.value;
            const purchasePrice = parseFloat(document.getElementById('purchase-price').value);
            const quantity = parseFloat(document.getElementById('crypto-quantity').value);

            if (!cryptoId || !purchasePrice || !quantity) {
                alert('Veuillez remplir tous les champs');
                return;
            }

            try {
                const response = await fetch(SERVER_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'add',
                        user_id: userId,
                        crypto_id: cryptoId,
                        crypto_name: cryptoName,
                        purchase_price: purchasePrice,
                        quantity: quantity
                    })
                });

                if (!response.ok) throw new Error('Erreur lors de l\'ajout');

                // Réinitialisation du formulaire
                cryptoSearch.value = '';
                selectedCryptoId.value = '';
                document.getElementById('purchase-price').value = '';
                document.getElementById('crypto-quantity').value = '';

                // Rechargement du wallet
                loadWallet();

            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout au portefeuille');
            }
        });

        // 4. Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initComparisonChart();
            loadWallet();

            // Gestion de la suppression
            walletHoldings.addEventListener('click', async (e) => {
                if (e.target.classList.contains('delete-btn')) {
                    if (!confirm('Supprimer cette crypto de votre portefeuille ?')) return;

                    const cryptoId = e.target.getAttribute('data-id');
                    try {
                        const response = await fetch(SERVER_API, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'delete',
                                user_id: userId,
                                crypto_id: cryptoId
                            })
                        });

                        if (!response.ok) throw new Error('Erreur lors de la suppression');
                        loadWallet();

                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression');
                    }
                }
            });
        });
    </script>
</body>

<?php require_once '../includes/footer.php'; ?>
</html>