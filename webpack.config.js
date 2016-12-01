var path = require('path');
var webpack = require('webpack');
 
module.exports = {
  entry: './resources/assets/js/component.jsx',
  output: { path: __dirname, filename: 'component.js' },
  module: {
    loaders: [
      {
        test: /.jsx?$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        query: {
          presets: ['es2015', 'react']
        }
      }
      ,{ test: /\.css$/, loader: "style-loader!css-loader" },
    ]
  },
};