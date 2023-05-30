# Roadmap

## Short Term

### Privacy.md

A PRIVACY.md file should be added.

### Mediawiki code

The mediawiki code (`mediawiki/Title.php` and `ilWikiUtil`) is currently more or less "untouched". It should be put under unit tests and integrated in our code. Unused parts should be removed. (partly done for ILIAS 9)

### JS Code

The JS code should be transferred to ES6 modules. jQuery use should be eliminated.

### Service Architecture

A cleaner separation of data, repository, domain and GUI code should be introduced.

### Replace static methods

Static methods should be replaced by non-static variants in data, repository, domain or gui classes.

### Strict type handling

declare(strict_types=1); should be added to all classes.

## Mid Term


## Long Term