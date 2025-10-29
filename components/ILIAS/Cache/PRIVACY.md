# Cache Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Components use the Cache component for writing data to a cache as well as reading or deleting data from a cache.
- There are several caching options whose distinctions will be outlined in the following sections if they are relevant to matters of privacy.
- Components which use the Cache component might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Data being stored
- The Cache component itself does not collect personal data. 
- The Cache component only manges data handed over by other components.
- The Cache component is ignorant of any personal data handed over by components.

## Data being presented
- The Cache component does not present any personal data.

## Data being deleted
- The Cache component deletes data if manually prompted to do so by components or automatically if the time limit on the life time of a data entry has expired. 
- It can be determined in the config.json file of the ILIAS setup whether or not the Cache has the option to delete entries automatically by looking up the used caching option:
  - Caching options with automatic deletion: Apc, Memcache
  - Caching options without automatic deletion: Static Cache

## Data being exported
- The Cache component does not export any personal data.
