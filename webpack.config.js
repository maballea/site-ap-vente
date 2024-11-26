const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // Public path used by the web server to access the output path
    .setPublicPath('/build')
    // Only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * Entry configuration
     *
     * Each entry will result in one JavaScript file (e.g., app.js)
     * and one CSS file (e.g., app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js') // Point d'entrée de l'application
    .enableVueLoader()                  // Active le support Vue.js
    .enableSassLoader()                 // Active le support Sass/SCSS
    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()                 // Divise les fichiers d'entrée en morceaux plus petits

    // Will require an extra script tag for runtime.js
    // But, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()         // Crée un fichier runtime.js séparé

    /*
     * Feature configuration
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()         // Supprime les anciens fichiers avant de construire les nouveaux
    .enableBuildNotifications()         // Active les notifications de build
    .enableSourceMaps(!Encore.isProduction()) // Active les sourcemaps en mode dev
    .enableVersioning(Encore.isProduction()) // Active le versioning pour les fichiers en mode production

    // Configure Babel
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage'; // Permet d'ajouter les polyfills nécessaires
        config.corejs = '3.38';       // Spécifie la version de core-js
    })

    // Uncomment to enable TypeScript support
    //.enableTypeScriptLoader()

    // Uncomment to enable React preset (if needed)
    //.enableReactPreset()

    // Uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // Uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

;

module.exports = Encore.getWebpackConfig();