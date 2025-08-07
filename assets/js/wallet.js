// Configuration
const COINGECKO_API = 'https://api.coingecko.com/api/v3';
const SERVER_API = '/wallet/wallet.php'; 

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