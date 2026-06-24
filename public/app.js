const defaultCoaches = [
  {
    id: "vip-45",
    name: "Autocar VIP 45 places",
    category: "vip",
    label: "VIP",
    capacity: 45,
    status: "Disponible",
    description: "Ideal pour les delegations, directions, evenements officiels et missions sensibles.",
    equipment: ["Climatisation", "Sieges inclinables", "Ecran TV", "Micro", "USB", "Grand bagage"],
  },
  {
    id: "premium-50",
    name: "Autocar Premium 50 places",
    category: "premium",
    label: "Premium",
    capacity: 50,
    status: "Disponible",
    description: "Le format polyvalent pour seminaires, sorties scolaires, tourisme et ceremonies.",
    equipment: ["Climatisation", "TV", "Sonorisation", "Sieges confort", "Bagagerie", "Chauffeur UTB"],
  },
  {
    id: "tourisme-60",
    name: "Grand Tourisme 60 places",
    category: "grand-tourisme",
    label: "Grand tourisme",
    capacity: 60,
    status: "Sur demande",
    description: "Grande capacite pour voyages de groupe, associations, clubs sportifs et longues distances.",
    equipment: ["Climatisation", "Toilettes selon parc", "TV", "Micro", "Bagagerie XL", "Longue distance"],
  },
  {
    id: "standard-30",
    name: "Car Confort 30 places",
    category: "premium",
    label: "Confort",
    capacity: 30,
    status: "Disponible",
    description: "Une solution agile pour groupes reduits, formations, transferts et sorties d'une journee.",
    equipment: ["Climatisation", "Sieges confort", "Bagages", "Trajets urbains", "Excursions", "Chauffeur"],
  },
];

let coaches = JSON.parse(localStorage.getItem("utbCoaches") || "null") || defaultCoaches;
const defaultContent = {
  heroTitle: "Location d'autocars de luxe pour vos voyages, evenements et missions.",
  heroText: "Une experience de reservation claire, un parc valorise, des demandes suivies et un retour qualite apres chaque prestation.",
  catalogTitle: "Choisir le bon car selon la capacite et le standing.",
  phone: "+225 07 00 00 00 00",
  email: "location@utb.ci",
  whatsapp: "2250700000000",
};
const publicContent = { ...defaultContent, ...(JSON.parse(localStorage.getItem("utbPublicContent") || "null") || {}) };

function applyPublicContent() {
  const heroTitle=document.querySelector("#publicHeroTitle"), heroText=document.querySelector("#publicHeroText"), catalogTitle=document.querySelector("[data-public-content=\"catalogTitle\"]"), phone=document.querySelector("#publicPhone"), email=document.querySelector("#publicEmail"), whatsapp=document.querySelector("#publicWhatsapp");
  if(heroTitle) heroTitle.textContent=publicContent.heroTitle;
  if(heroText) heroText.textContent=publicContent.heroText;
  if(catalogTitle) catalogTitle.textContent=publicContent.catalogTitle;
  if(phone) phone.href=`tel:${publicContent.phone.replace(/\s+/g,"")}`;
  if(email){email.textContent=publicContent.email;email.href=`mailto:${publicContent.email}`;}
  if(whatsapp) whatsapp.href=`https://wa.me/${publicContent.whatsapp.replace(/\D/g,"")}`;
}

const starterRequests = [
  { ref: "UTB-LOC-2026-0001", name: "Direction Regionale", route: "Abidjan - Yamoussoukro", status: "Devis envoye" },
  { ref: "UTB-LOC-2026-0002", name: "Groupe scolaire", route: "Cocody - Grand-Bassam", status: "En cours" },
  { ref: "UTB-LOC-2026-0003", name: "Association Lumiere", route: "Abidjan - Bouake", status: "Nouvelle demande" },
];

const fleetGrid = document.querySelector("#fleetGrid");
const vehicleDetail = document.querySelector("#vehicleDetail");
const coachSelect = document.querySelector("#coachSelect");
const quoteForm = document.querySelector("#quoteForm");
const quoteResult = document.querySelector("#quoteResult");

let requests = JSON.parse(localStorage.getItem("utbRequests") || "null") || starterRequests;

function renderFleet(filter = "all") {
  if (!fleetGrid) return;
  const visible = filter === "all" ? coaches : coaches.filter((coach) => coach.category === filter);

  fleetGrid.innerHTML = visible
    .map(
      (coach) => `
        <article class="coach-card">
          ${coach.image ? `<img class="coach-card-image" src="${coach.image}" alt="${coach.name}">` : `<div class="coach-visual" aria-hidden="true"></div>`}
          <div>
            <div class="coach-meta">
              <span class="badge">${coach.label}</span>
              <span class="badge">${coach.capacity} places</span>
              <span class="badge">${coach.status}</span>
            </div>
            <h3>${coach.name}</h3>
            <p>${coach.description}</p>
          </div>
          <button class="btn secondary" type="button" data-coach="${coach.id}">Voir la fiche</button>
        </article>
      `
    )
    .join("");
}

function renderCoachOptions() {
  if (!coachSelect) return;
  coachSelect.innerHTML = coaches
    .map((coach) => `<option value="${coach.name}">${coach.name} - ${coach.capacity} places</option>`)
    .join("");
}

function showVehicle(id) {
  if (!vehicleDetail) return;
  const coach = coaches.find((item) => item.id === id);
  if (!coach) return;
  const images = coach.images?.length ? coach.images : coach.image ? [coach.image] : [];

  vehicleDetail.classList.add("active");
  vehicleDetail.innerHTML = `
    <div class="detail-inner">
      ${images.length ? `<div class="detail-gallery"><img class="detail-photo detail-photo-image" id="detailMainImage" src="${images[0]}" alt="${coach.name}"><div class="detail-thumbnails">${images.map((image,index)=>`<button type="button" data-gallery-image="${image}" aria-label="Afficher la photo ${index+1}"><img src="${image}" alt=""></button>`).join("")}</div></div>` : `<div class="detail-photo" aria-hidden="true"></div>`}
      <div>
        <p class="eyebrow">Fiche vehicule</p>
        <h2>${coach.name}</h2>
        <p>${coach.description}</p>
        <div class="coach-meta">
          <span class="badge">${coach.label}</span>
          <span class="badge">${coach.capacity} places</span>
          <span class="badge">${coach.status}</span>
        </div>
        <h3>Equipements disponibles</h3>
        <ul class="equipment-list">
          ${coach.equipment.map((item) => `<li>${item}</li>`).join("")}
        </ul>
        <a class="btn primary" href="#devis" data-select-coach="${coach.name}">Demander un devis pour ce vehicule</a>
      </div>
    </div>
  `;
  vehicleDetail.scrollIntoView({ behavior: "smooth", block: "start" });
}

function saveRequests() {
  localStorage.setItem("utbRequests", JSON.stringify(requests));
}

function buildReference() {
  const number = String(requests.length + 1).padStart(4, "0");
  return `UTB-LOC-2026-${number}`;
}

function handleQuoteSubmit(event) {
  event.preventDefault();
  const data = new FormData(quoteForm);
  const ref = buildReference();
  const request = {
    ref,
    name: data.get("organization") || data.get("name"),
    route: `${data.get("origin")} - ${data.get("destination")}`,
    status: "Nouvelle demande",
  };

  requests = [request, ...requests];
  saveRequests();
  quoteForm.reset();
  renderCoachOptions();
  quoteResult.textContent = `Votre demande de devis a bien ete envoyee. Reference: ${ref}. Le service location UTB vous contactera dans les meilleurs delais.`;
}

document.querySelectorAll(".filter").forEach((button) => {
  button.addEventListener("click", () => {
    document.querySelectorAll(".filter").forEach((item) => item.classList.remove("active"));
    button.classList.add("active");
    renderFleet(button.dataset.filter);
  });
});

if (fleetGrid) {
  fleetGrid.addEventListener("click", (event) => {
    const button = event.target.closest("[data-coach]");
    if (button) showVehicle(button.dataset.coach);
  });
}

if (vehicleDetail) {
  vehicleDetail.addEventListener("click", (event) => {
    const thumbnail = event.target.closest("[data-gallery-image]");
    if (thumbnail) {
      const mainImage = document.querySelector("#detailMainImage");
      if (mainImage) mainImage.src = thumbnail.dataset.galleryImage;
      return;
    }
    const link = event.target.closest("[data-select-coach]");
    if (!link || !coachSelect) return;
    coachSelect.value = link.dataset.selectCoach;
  });
}

if (quoteForm) {
  quoteForm.addEventListener("submit", handleQuoteSubmit);
}

applyPublicContent();
renderFleet();
renderCoachOptions();
