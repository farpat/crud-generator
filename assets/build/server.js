const WebpackDevServer = require('webpack-dev-server');
const configWebpack = require('./webpack.config');
const webpack = require('webpack');
const compiler = webpack(configWebpack);
const hotMiddleware = require('webpack-hot-middleware')(compiler);
const chokidar = require('chokidar');
const config = require('./config');

let refresh = function (path) {
    console.log('* ' + path + ' changed');
    hotMiddleware.publish({action: 'reload'});
};


let server = new WebpackDevServer(compiler, {
    hot: true,
    quiet: true,
    noInfo: false,
    publicPath: configWebpack.output.publicPath,
    overlay: true,
    stats: {
        colors: true,
        chunks: false
    },
    headers: {
        "Access-Control-Allow-Origin": "*",
        "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
        "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
    }
});
server.use(hotMiddleware);
server.listen(config.port, 'localhost', function (err) {
    if (err) {
        console.log(err);
        return;
    }

    chokidar.watch(config.refresh).on('change', refresh);
    console.log('==> Listening on http://localhost:' + config.port);
});