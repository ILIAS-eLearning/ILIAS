# Roadmap

## Short Term

### Documentation on current service API

A README.md should be added to document the intended use by consuming components. A business rule should state where to place the style settings, see discussion under https://docu.ilias.de/goto_docu_wiki_wpage_6761_1357.html

## Mid Term

### Clarify dependency/relation with COPage component

Content style related files are currently spread over two components: COPage and Style/Content. This should be reorganised. Since the dependency is quite strong and the content styles are never been meant be used in other components, the code located currently in Style/Content most probably will be moved to the COPage component in the future.

### Allow to copy classes within same style

Currently copies can only be created in other style objects. This limitation should be eliminated.

## Long Term

