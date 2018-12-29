const webpack = require('webpack');
const config = require('./config');
const path = require('path');
const debug = process.env.NODE_ENV === 'development';

const {VueLoaderPlugin} = require('vue-loader');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const imageminMozjpeg = require('imagemin-mozjpeg');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const WebpackBundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const ManifestPlugin = require('webpack-manifest-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const chokidar = require('chokidar');

let configWebpack = {
    devServer: {
        before(app, server) {
            chokidar.watch(config.refresh).on('change', function () {
                server.sockWrite(server.sockets, 'content-changed');
            })
        },
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization'
        },
        port: config.port,
        overlay: true,
        clientLogLevel: 'warning',
        publicPath: (debug ? ('http://localhost:' + config.port) : '') + '/assets/',
        stats: {
            colors: true,
            chunks: false
        },
    },
    mode: debug ? 'development' : 'production',
    devtool: debug ? 'cheap-module-eval-source-map' : false,
    entry: config.entry,
    output: {
        path: path.resolve('./public/assets'),
        filename: debug ? '[name].js' : '[name].[chunkhash:4].js',
        publicPath: (debug ? ('http://localhost:' + config.port) : '') + '/assets/'
    },
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            vue: 'vue/dist/vue.js'
        }
    },
    module: {
        rules: [
            //scss
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: [
                        {
                            loader: 'css-loader',
                            options: {
                                minimize: !debug,
                                sourceMap: debug
                            }
                        },
                        {
                            loader: 'postcss-loader',
                            options: {
                                sourceMap: debug,
                            }
                        },
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: debug
                            }
                        }
                    ]
                })
            },
            //vue
            {
                test: /\.vue$/,
                exclude: /node_modules/,
                loader: 'vue-loader'
            },
            //js
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader'
            },
            //fonts
            {
                test: /\.(woff2?|eot|ttf|otf)(\?.*)?$/,
                loader: 'file-loader',
                options: {
                    name: 'fonts/[name]-[hash:3].[ext]',
                }
            },
            //images
            {
                test: /\.(png|jpe?g|gif|svg)$/,
                use: [
                    {
                        loader: 'url-loader',
                        options: {
                            limit: 8192,
                            name: 'images/[name].[ext]'
                        }
                    },
                    {
                        loader: 'img-loader',
                        options: {
                            enabled: debug
                        }
                    }
                ]
            },
        ]
    },
    plugins: [
        new VueLoaderPlugin(),

        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            // Popper: 'popper.js',
        }),

        new ExtractTextPlugin({
            filename: '[name].[hash:4].css',
            disable: debug
        }),
        new ImageminPlugin({
            disable: debug,
            pngquant: {quality: '95-100'},
            jpegtran: false,
            plugins: [
                imageminMozjpeg({quality: 90, progressive: true})
            ]
        })
    ],
};

if (!debug) {
    configWebpack.plugins.push(
        new WebpackBundleAnalyzerPlugin({
            analyzerMode: 'static',
            openAnalyzer: false,
        }),

        new ManifestPlugin({
            filter: (file) => {
                return !file.name.startsWith('img');
            }
        }),

        new CleanWebpackPlugin(['assets'], {
            root: path.resolve('./public'),
        }));
}

module.exports = configWebpack;
