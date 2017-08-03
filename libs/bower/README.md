# How to add dependencies with bower in ILIAS

**New dependencies need to be approved by the Jour Fixe of the ILIAS society.**

## Dependencies for production
- Comment all lines in libs/.gitignore which begin with bower/
- Add a new library using bower, e.g. "bower install bootstrap@3.3.7 --save"
- Add a section in "extra" with the following metadata":
```json
 "jquery": {
      "introduction": "03.08.2017",
      "introduced-by": "fschmid"
    },
```
- Run "bower install --no-dev"
- Add all files to ILIAS git-repository and commit

## Dependencies for development
- Add a new library using bower, e.g. "bower install mocha --save-dev" 
- Ignore all directories which are added by installation (uncomment existing)
- commit changes in gitignore and bower.json.
