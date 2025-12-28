/** @type {import('tailwindcss').Config} */

/*
|--------------------------------------------------------------------------
| CRUD Generator - Tailwind Configuratie
|--------------------------------------------------------------------------
|
| Dit bestand bevat de kleurenconfiguratie voor de CRUD generator.
| Kopieer dit naar je project of merge het met je bestaande tailwind.config.js
|
| Gebruik: npx tailwindcss -i ./resources/css/app.css -o ./public/css/app.css
|
*/

export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  
  theme: {
    extend: {
      colors: {
        // CRUD Generator kleuren - pas deze aan naar wens
        crud: {
          // Primaire kleur (buttons, links, focus states)
          primary: {
            50:  'var(--crud-primary-50, #eef2ff)',
            100: 'var(--crud-primary-100, #e0e7ff)',
            200: 'var(--crud-primary-200, #c7d2fe)',
            300: 'var(--crud-primary-300, #a5b4fc)',
            400: 'var(--crud-primary-400, #818cf8)',
            500: 'var(--crud-primary-500, #6366f1)',
            600: 'var(--crud-primary-600, #4f46e5)',
            700: 'var(--crud-primary-700, #4338ca)',
            800: 'var(--crud-primary-800, #3730a3)',
            900: 'var(--crud-primary-900, #312e81)',
            950: 'var(--crud-primary-950, #1e1b4b)',
          },
        },
      },
    },
  },
  
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
