# Client side third-party libraries

The update management of third-party libraries can become a complex task, especially if there is a larger number of dependencies. We would like to keep these dependencies as low as possible.

Before proposing a new library please
- check if standard CSS/HTML can fit your needs (e.g. effects, animation) and
- try to avoid libraries that create dependencies to other libraries.

**New dependencies MUST be approved by the Jour Fixe of the ILIAS society or by the Technical Board.**


## Use of jQuery

jQuery has played a major role for our Javascript code in the past, however we believe that since ES6 and above got widely supported in all relevant browsers, using standard Javascript might be a better choice in many cases.

- Use Standard Javascript whenever possible.
- Avoid jQuery-dependent libraries whenever possible, prefer third-party libraries without dependencies. 


## npm

npm is used in ILIAS mostly to organise client side js and css libraries.

To propose a new dependency, create a Pull Request on GitHub that contains the
proposed changes to `package.json`, name it like "Add library XYZ" and assign
the "jour fixe"-label.

## Dependencies for production
- Install the new library, e.g. "npm install bootstrap@3.3.7"
- Add a section in "extra" with the following metadata":
```
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
- Also commit the changes in node_modules.

Currently commit the node_modes directory, this will most probably be abendoned from the git repo in the future.

## Custom Patches on Dependencies
If a patch on a library is requried, use the "patch-packages"-module.
To create the patch, modify the file (in src!) and run
```
npx patch-package [package-name]
```
Commit the new patch-file.
There is a hook in package.json/scripts that will apply the patch on installation of the library.
