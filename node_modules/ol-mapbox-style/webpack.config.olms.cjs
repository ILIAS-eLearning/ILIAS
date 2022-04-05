const path = require('path');

const externals = {
  'ol/style/Style': 'ol.style.Style',
  'ol/style/Circle': 'ol.style.Circle',
  'ol/style/Icon': 'ol.style.Icon',
  'ol/style/Stroke': 'ol.style.Stroke',
  'ol/style/Fill': 'ol.style.Fill',
  'ol/style/Text': 'ol.style.Text',
  'ol/proj': 'ol.proj',
  'ol/tilegrid': 'ol.tilegrid',
  'ol/tilegrid/TileGrid': 'ol.tilegrid.TileGrid',
  'ol/format/GeoJSON': 'ol.format.GeoJSON',
  'ol/format/MVT': 'ol.format.MVT',
  'ol/Map': 'ol.Map',
  'ol/View': 'ol.View',
  'ol/Observable': 'ol.Observable',
  'ol/layer/Tile': 'ol.layer.Tile',
  'ol/layer/Vector': 'ol.layer.Vector',
  'ol/layer/VectorTile': 'ol.layer.VectorTile',
  'ol/source/TileJSON': 'ol.source.TileJSON',
  'ol/source/Vector': 'ol.source.Vector',
  'ol/source/VectorTile': 'ol.source.VectorTile',
};

function createExternals() {
  const createdExternals = {};
  for (const key in externals) {
    createdExternals[key] = {
      root: externals[key].split('.'),
      commonjs: key,
      commonjs2: key,
      amd: key,
    };
  }
  return createdExternals;
}

module.exports = {
  entry: './src/olms.js',
  devtool: 'source-map',
  mode: 'production',
  module: {
    rules: [
      {
        test: /\.js$/,
        include: [__dirname],
        use: {
          loader: 'buble-loader',
          options: {
            transforms: {dangerousForOf: true},
          },
        },
      },
    ],
  },
  output: {
    path: path.resolve('./dist'), // Path of output file
    filename: 'olms.js',
    library: 'olms',
    libraryTarget: 'umd',
    libraryExport: 'default',
  },
  externals: createExternals(),
};
