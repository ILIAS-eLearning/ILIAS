# Roadmap

## Short Term

...

## Mid Term

- Refactor Javascript, e.g. remove use of jQuery
- Improve DI handling to get more code under unit tests
- Add a decent internal service structure
- Use namespaces
- Improve external API and documentation

### Use multiple help modules

It should be possible to import and activate more than one help module. This will make the handling of help content more flexible and support customised content, e.g. for plugins, see https://docu.ilias.de/goto_docu_wiki_wpage_3306_1357.html

### Refactor / Abandon Tooltips

The legacy tooltip implementation is a constant source of issues, mostly tooltips are not disappearing which might be due to an side effect occuring in ILIAS when the qTip library is being used. Either this feature is abandoned or moved to another UI component. Additionally the accessibility of the feature needs to be ensured.