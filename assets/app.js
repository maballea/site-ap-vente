import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './Css/styles.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
import { createApp } from 'vue';
import App from './components/App.vue';

// Initialiser l'application Vue
createApp(App).mount('#app');