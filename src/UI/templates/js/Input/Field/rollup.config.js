import {terser} from "rollup-plugin-terser";

export default {
    input: './src/input.factory.js',
    output: {
        file: './dist/input.factory.min.js',
        format: 'es',
        // plugins: [terser()],
    },
};
