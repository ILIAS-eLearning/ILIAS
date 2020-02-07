# Typescript Guidelines

- You MAY (or SHOULD?) use Typescript (version 3.0/3.4?) to generate Javascript code.
- Typescript source files should be located in a subfolder `js/src`. The generated Javascript files should be located in a subfolder `js/build` on the same level, e.g. `Services/Component/js/src/component.ts` and `Services/Component/js/build/component.js`.
- A typescript.json file should be located in the  components `js` folder, e.g. `Services/Component/js/typescript.json`.
- You SHOULD use "es6" as a target for the compiler.
- You SHOULD activate "sourceMap" in the compiler options.

## Roadmap

- If we agree to use StandardJS as a coding style, we need documentation on how to apply this to Typescript.
- Our commitment to Typescript (MAY or SHOULD be used) will influence future decisions, e.g. when it comes to tools and guidelines for dependency injection management.
- If multiple components start to use Typescript to provide services guidelines to handle references to other components seem to be needed. Also a way to manage declaration files (.d.ts) files, e.g. for non typescript dependencies like jQuery is needed.