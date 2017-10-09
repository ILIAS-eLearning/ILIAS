# How to add dependencies with bower in ILIAS

**New dependencies MUST be approved by the Jour Fixe of the ILIAS society or by the Technical Board.**

## Dependencies for production
- Comment all lines in libs/.gitignore which begin with bower/
- Add a new library using bower, e.g. "bower install bootstrap@3.3.7 --save"
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
- Run "bower install"
- Add all files to ILIAS git-repository and commit

## Dependencies for development
- Add a new library using bower, e.g. "bower install mocha --save-dev" 
- Ignore all directories which are added by installation (uncomment existing)
- commit changes in gitignore and bower.json.
