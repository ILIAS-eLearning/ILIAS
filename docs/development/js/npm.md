# npm

npm is used in ILIAS mostly to organise client side js and css libraries.

**New dependencies MUST be approved by the Jour Fixe of the ILIAS society or by the Technical Board.**

To propose a new dependency, create a Pull Request on GitHub that contains the
proposed changes to `package.json`, name it like "Add library XYZ" and assign
the "jour fixe"-label.

## Dependencies for production
- Install the new library, e.g. "npm install bootstrap@3.3.7"
- Add a section in "extra" with the following metadata":
```json
 "jquery": {
      "introduction-date": "2017-08-03",
      "approved-by": "Technical Board", // "Technical Board" or "Jour Fixe"
      "developer": "Username of the developer which introduced to Library",
      "purpose": "Describe the reason why this library is needed in ILIAS.",
      "last-update-for-ilias": "5.3.0" // ILIAS Version that last updated this Library
    },
```
- Commit the changes in packages.json and package-lock.json and node_modules.

## Dependencies for development
- Install the new library, e.g. "npm install webpack --save-dev"
- Commit the changes in packages.json and package-lock.json

- Commit the changes in packages.json and package-lock.json and node_modules.

Currently commit the node_modes directory, this will most probably be abendoned from the git repo in the future.