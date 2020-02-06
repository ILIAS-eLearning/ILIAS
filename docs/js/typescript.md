# Typescript Guidelines

- You MAY use Typescript to generate Javascript code.
- Typescript source files should be located in a subfolder `js/src`. The generated Javascript files should be located in a subfolder `js/build` on the same level, e.g. `Services/Component/js/src/component.ts` and `Services/Component/js/build/component.js`.
- A typescript.json file should be located in the  components `js` folder, e.g. `Services/Component/js/typescript.json`.
- You SHOULD use "es6" as a target for the compiler.