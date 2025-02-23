/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js", // Vérifie les fichiers JS dans le dossier assets
    "./templates/**/*.html.twig", // Vérifie les fichiers Twig dans le dossier templates
  ],
  theme: {
    // screens: {
    //   'mobile': '430px',  
    //   'tablet': '640px', 
    //   'laptop': '1024px', 
    //   'desktop': '1280px' 
    // },
    extend: {
      colors: {
        'custom-blue': '#14213D',
        'custom-sky-blue': '#0284C7',
        'custom-hover': '#b49047',
        'custom-login':'#1E3A8A',
      },
      fontSize: {
        'xxs': '0.625rem',
      },
      fontFamily: {
  sans: ['sans-serif'],
},

    },
  },
  plugins: [],
};