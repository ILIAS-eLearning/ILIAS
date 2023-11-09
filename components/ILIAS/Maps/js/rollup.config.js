import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
    input: './src/ilOl.js',
    output: {
        file: './dist/ServiceOpenLayers.js',
        format: 'es'
    },
    plugins: [resolve(), commonjs()],
    onwarn: function(warning, superOnWarn) {
        /*
         * skip certain warnings
         * https://github.com/openlayers/openlayers/issues/10245
         */
        if (warning.code === 'THIS_IS_UNDEFINED') {
            return;
        }
        superOnWarn(warning);
    }
};