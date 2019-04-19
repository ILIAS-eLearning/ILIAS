# How to add dependencies with Yarn in ILIAS

This document describes how to manage dependencies with [Yarn](https://yarnpkg.com/).

**New dependencies MUST be approved by the Jour Fixe of the ILIAS society or by the Technical Board.**

To propose a new dependency, create a Pull Request on GitHub that contains the
proposed changes to `package.json`, name it like "Add library XYZ" and assign
the "jour fixe"-label.

## Adding dependencies for production

- If you want to add a package as dependency, you can use the following Yarn command:
```bash
yarn add [package]
yarn add tippy.js
```

- You can also add a specific version of a package by using the following syntax:
```bash
yarn add [package]@[version]
yarn add tippy.js@4.1
```

- After adding a package, please add a section in "extra" with the following metadata":
```json
 "jquery": {
      "introduction-date": "2017-08-03",
      "approved-by": "Technical Board", // "Technical Board" or "Jour Fixe"
      "developer": "Username of the developer which introduced to Library",
      "purpose": "Describe the reason why this library is needed in ILIAS.",
      "last-update-for-ilias": "5.3.0" // ILIAS Version that last updated this Library
    },
```
- Add all files to ILIAS git-repository and commit

## Upgrading a dependency

- If you want to upgrade any dependency that you have already added,
you can use the following command syntax to upgrade it (to the latest
version or the range defined in the [package.json](./package.json) file):
```bash
yarn upgrade [package]
yarn upgrade tippy.js
```

- If you want to upgrade a package to a specific version you can use the following syntax:
```bash
yarn upgrade [package]@[version]
yarn upgrade tippy.js@4.2
```

- Upgrading all packages can be achieved by typing:
```bash
 yarn upgrade
 ```

## Removing a dependency

- Dependencies can be removed by the following command syntax:
```bash
yarn remove [package]
yarn remove tippy.js
```

## Installing all the dependencies of project
 
```bash
yarn install
```

## Dependencies for development

```bash
yarn add tippy.js --dev
```