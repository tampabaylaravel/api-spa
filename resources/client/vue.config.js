const isProduction = process.env.NODE_ENV === 'production'

module.exports = {
    outputDir: '../../public/dist',
    publicPath: isProduction ? 'dist' : '', // workaround

    devServer: {
        disableHostCheck: true,
        proxy: {
            '/api': {
                target: `http://localhost`,
                changeOrigin: true
            }
        }
    }
}
