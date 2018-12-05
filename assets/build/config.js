module.exports = {
    entry: {
        app: [
            './assets/sass/app.scss',

            './assets/js/app.js',
        ]
    },
    port: 3003,
    refresh: ['templates/**/*.twig'],
};