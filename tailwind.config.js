/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/vue/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        'primary-blue': '#2563eb',
        'accent-green': '#10b981',
        'bg-light': '#f8fafc',
      },
    },
  },
  plugins: [],
}
