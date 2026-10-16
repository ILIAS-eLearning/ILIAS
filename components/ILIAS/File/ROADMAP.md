# Roadmap

## Short Term

### Implement WOPI Interface

The Feature [Implement WOPI for File-Editing](https://docu.ilias.de/goto_docu_wiki_wpage_7403_1357.html) will hopefully
be implemented in the near future. This will allow for editing files in ILIAS using Office Online. The goal is to
implement the WOPI Interface in the File module.

## Mid Term

### Remove all Public Methods which are used outside the Module

There are several other components which use the File Objects "as a service". This is not a good practice and should be
avoided. The goal is to remove all public methods which are used outside the File module.

### Align the Activities with the Activities Framework

The Activities in `src/Activities` work around a few gaps of the Activities framework and of the
services offered by other components. The workarounds are described in `src/Activities/README.md`,
each with the condition under which it can be removed. The goal is to remove them as soon as:

* `maybePerformAs()` reads raw parameters through the input description, so that
  `FileUpload\Activities\Parameter` is no longer needed,
* the framework offers a base class for Commands,
* the repository tree, the object definition, the settings and the current user are provided by
  their components, so that `RepositoryProvider` no longer reads the legacy container,
* objects can be created, copied and deleted for a given user, not only for the user of the session,
* the Repository component offers a public deletion service to replace `ilRepUtil::deleteObjects()`.

## Long Term

### Currently no long term goals


