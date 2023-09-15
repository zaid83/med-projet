// Création des icônes pour la carte
var LeafIcon = L.Icon.extend({
  options: {
    iconSize: [38, 50],
    // shadowSize: [50, 64],
    // iconAnchor: [22, 94],
    // shadowAnchor: [4, 62],
    popupAnchor: [-3, -40],
  },
});

var blackIcon = new LeafIcon({ iconUrl: "public/assets/icons/black.png" }),
  maroonIcon = new LeafIcon({ iconUrl: "public/assets/icons/maroon.png" }),
  redIcon = new LeafIcon({ iconUrl: "public/assets/icons/red.png" }),
  orangeIcon = new LeafIcon({ iconUrl: "public/assets/icons/orange.png" }),
  yellowIcon = new LeafIcon({ iconUrl: "public/assets/icons/yellow.png" });

// Conversion de la position en décimal
function convertDMSToDecimal(degrees, minutes, seconds, direction) {
  // Calcul du degré décimal
  let decimalDegrees = degrees + minutes / 60 + seconds / 3600;

  // Vérification de la direction (N, S, E, W)
  if (direction === "S" || direction === "W") {
    decimalDegrees = -decimalDegrees;
  }

  return decimalDegrees;
}
// Récupération des données de tri
// function getTotal() {
var totalPlastic = [];
fetch("Data/plastiqueSum", {
  method: "GET",
  headers: { "Content-Type": "application/json" },
})
  .then(function (response) {
    return response.json();
  })
  .then(function (response) {
    response.forEach((element) => {
      totalPlastic.push(element["total"]);
    });
    return totalPlastic;
  });
// }

// Récupération des données de prélèvement
fetch("Data/data", {
  method: "GET",
  headers: { "Content-Type": "application/json" },
})
  .then(function (response) {
    return response.json();
  })
  .then(function (response) {
    let i = 0;
    response.forEach((element) => {
      let latitude = element["Start_Latitude"];
      latitude = latitude.split(/[^\d\w]+/);
      let latitudeDecimal = convertDMSToDecimal(
        parseInt(latitude[0]),
        parseInt(latitude[1]),
        parseInt(latitude[2]),
        parseInt(latitude[3])
      ).toFixed(2);
      let longitude = element["Start_Longitude"];
      longitude = longitude.split(/[^\d\w]+/);
      let longitudeDecimal = convertDMSToDecimal(
        parseInt(longitude[0]),
        parseInt(longitude[1]),
        parseInt(longitude[2]),
        parseInt(longitude[3])
      ).toFixed(2);
      if (totalPlastic[i] > 500) {
        var marker = L.marker([latitudeDecimal, longitudeDecimal], {
          icon: blackIcon,
        });
      } else if (totalPlastic[i] >= 300 && totalPlastic[i] < 500) {
        var marker = L.marker([latitudeDecimal, longitudeDecimal], {
          icon: maroonIcon,
        });
      } else if (totalPlastic[i] >= 100 && totalPlastic[i] < 300) {
        var marker = L.marker([latitudeDecimal, longitudeDecimal], {
          icon: redIcon,
        });
      } else if (totalPlastic[i] >= 50 && totalPlastic[i] < 100) {
        var marker = L.marker([latitudeDecimal, longitudeDecimal], {
          icon: orangeIcon,
        });
      } else if (totalPlastic[i] >= 0 && totalPlastic[i] < 50) {
        var marker = L.marker([latitudeDecimal, longitudeDecimal], {
          icon: yellowIcon,
        });
      }
      marker.addTo(map);
      marker.bindPopup(`
        <b>Echantillon : <a href="data/detailBySample/${element["Sample"]}">${element["Sample"]}</a></b>
        <p><em>Microplastiques récoltés : ${totalPlastic[i]}</em></p>
        <p>Mer : ${element["Sea"]}</p>
        <p>Date : ${element["Date"]}</p>
        <p>Concentration au km2 : ${element["Concentration_km2"]}</p>
        <p>Concentration au m3 : ${element["Concentration_m3"]}</p>
        `);
      i++;
    });
  });

const mediterranean = {
  lat: 41,
  lng: 9,
};

const zoomLevel = 6;

var map = L.map("map").setView(
  [mediterranean.lat, mediterranean.lng],
  zoomLevel
);

L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution:
    '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
}).addTo(map);

// Coordonnées des zones de découpe
var mediterraneanSeaCoords = [
  [38, 0],
  [38, 15],
  [46, 15],
  [46, 0],
];

var tyrrhenianSeaCoords = [
  [38, 10],
  [38, 15],
  [42, 15],
  [42, 10],
];

// Coordonnées de la mer Ligure
var ligurianSeaCoords = [
  [43.5, 7.0],
  [43.5, 9.5],
  [44.0, 9.5],
  [44.0, 7.0],
];

// Coordonnées de la Mer de Sardaigne
var sardinianSeaCoords = [
  [39.0, 7.0],
  [39.0, 10.0],
  [42.0, 10.0],
  [42.0, 7.0],
];

// Coordonnées des Bouches de Bonifacio
var bonifacioStraitCoords = [
  [41.4, 9.1],
  [41.4, 9.5],
  [41.8, 9.5],
  [41.8, 9.1],
];

// Créer des polygones pour chaque zone de découpe
var mediterraneanSea = L.polygon(mediterraneanSeaCoords, {
  color: "blue",
  fillOpacity: 0.4,
}).addTo(map);
mediterraneanSea.bindPopup("Mer Méditerranée");

var tyrrhenianSea = L.polygon(tyrrhenianSeaCoords, {
  color: "green",
  fillOpacity: 0.4,
}).addTo(map);
tyrrhenianSea.bindPopup("Mer Tyrrhénienne");

// Créer un polygone pour la mer Ligure
var ligurianSea = L.polygon(ligurianSeaCoords, {
  color: "red",
  fillOpacity: 0.4,
}).addTo(map);
ligurianSea.bindPopup("Mer Ligurienne");

// Créer un polygone pour la Mer de Sardaigne
var sardinianSea = L.polygon(sardinianSeaCoords, {
  color: "purple",
  fillOpacity: 0.4,
}).addTo(map);
sardinianSea.bindPopup("Mer de Sardaigne");

// Créer un polygone pour les Bouches de Bonifacio
var bonifacioStrait = L.polygon(bonifacioStraitCoords, {
  color: "orange",
  fillOpacity: 0.4,
}).addTo(map);
bonifacioStrait.bindPopup("Bouches de Bonifacio");
