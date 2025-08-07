document.addEventListener("DOMContentLoaded", () => {
  const fallbackLang = 'fr'; // français par défaut
  const supportedLangs = ['en', 'es', 'pt', 'ru', 'de', 'nl', 'ar', 'it'];

  // Détecter langue du navigateur (ex: "en-US" → "en")
  let lang = navigator.language.slice(0, 2);
  if (!supportedLangs.includes(lang)) lang = fallbackLang;

  // Appliquer la langue sur la balise <html>
  document.documentElement.lang = lang;

  // Si langue RTL, activer RTL
  if (lang === 'ar') {
    document.documentElement.dir = 'rtl';
  }

  // Charger le fichier de langue correspondant
  if (lang !== 'fr') {
    fetch(`/lang/${lang}.json`)
      .then((res) => res.json())
      .then((data) => applyTranslations(data))
      .catch((err) => console.error("Erreur chargement langue:", err));
  }
});

function applyTranslations(data) {
  // Titre principal
  document.querySelector("[data-i18n='title']").textContent = data.title;

  // Cartes
  const cards = document.querySelectorAll("[data-i18n-card]");
  cards.forEach((card, i) => {
    const titleEl = card.querySelector("[data-i18n-card-title]");
    const textEl = card.querySelector("[data-i18n-card-text]");
    if (titleEl) titleEl.textContent = data.cards[i].title;
    if (textEl) textEl.textContent = data.cards[i].text;
  });
}
