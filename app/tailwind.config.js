/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./assets/**/*.js",
  "./templates/**/*.html.twig",],
  theme: {
    extend: {
      colors : {
        'hackyellow' : '#FFEFC7',
        'hackorange' :'#FF7A6B',
        'hackorangebis' : '#FFAAA0',
      }
    },
  },
  plugins: [],
}

