const { merge } = require('webpack-merge')
const FileManagerPlugin = require('filemanager-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const config = require('flarum-webpack-config');
const path = require('path');

module.exports = merge(config(), {
    output: {
        chunkFilename: 'chunk~[name].js'
    },
    plugins: [
        new CleanWebpackPlugin({
            dry: false,
            dangerouslyAllowCleanPatternsOutsideProject: true,
            cleanOnceBeforeBuildPatterns: [
                path.resolve(process.cwd(), '../assets/*'),
            ]
        }),
        new FileManagerPlugin({
            events: {
                onEnd: {
                    copy: [
                        { source: 'dist/chunk*', destination: '../assets/' },
                    ],
                },
            },
        }),
    ],
})
