// Sélectionne le bouton ou l'élément qui permet de basculer la visibilité du mot de passe
let togglePasswordVisibility = document.getElementById("togglePasswordVisibility");

// Sélectionne le champ de saisie du mot de passe
let input = document.getElementById("password");

// Ajoute un événement "click" au bouton pour gérer la visibilité du mot de passe
togglePasswordVisibility.addEventListener("click", function() {
    // Vérifie si le type de champ est actuellement "password" (mot de passe masqué)
    if (input.type === 'password') {
        // Si oui, change le type en "text" (mot de passe visible)
        input.type = 'text';
        // Change le texte ou l'icône du bouton pour indiquer que le mot de passe est visible
        togglePasswordVisibility.textContent = "👁️"; // Icône de l'œil ouvert
    } else {
        // Sinon, remet le type du champ en "password" (mot de passe masqué)
        input.type = 'password';
        // Change le texte ou l'icône du bouton pour indiquer que le mot de passe est masqué
        togglePasswordVisibility.textContent = "👁️‍🗨️"; // Icône de l'œil barré
    }
});
