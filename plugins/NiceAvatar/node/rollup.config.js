import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import replace from '@rollup/plugin-replace';
import terser from '@rollup/plugin-terser';

export default {
    input: 'src/nice-avatar.js',
    output: {
        file: '../js/nice-avatar.min.js',
        format: 'iife',
        name: 'NiceAvatar',
        sourcemap: false
    },
    plugins: [
        replace({
            preventAssignment: true,
            'process.env.NODE_ENV': JSON.stringify('production')
        }),
        resolve({
            browser: true
        }),
        commonjs(),
        terser()
    ]
};
