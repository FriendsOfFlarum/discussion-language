const { merge } = require('webpack-merge')
const FileManagerPlugin = require('filemanager-webpack-plugin');
const config = require('flarum-webpack-config');

module.exports = merge(config(), {
    output: {
        chunkFilename: 'vendors~[name].js'
    },
    plugins: [
        new FileManagerPlugin({
            events: {
                onEnd: {
                    copy: [
                        { source: 'dist/vendors*', destination: '../assets/' },
                    ],
                },
            },
        }),
    ],
})
