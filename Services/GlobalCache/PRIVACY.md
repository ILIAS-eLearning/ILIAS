# GlobalCache Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Services and components use the GlobalCache service for writing data to a cache as well as reading or deleting data from a cache.
- There are several caching options whose distinctions will be outlined in the following sections if they are relevant to matters of privacy.
- Services and components which use the GlobalCache service might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Data being stored
- The GlobalCache service itselfs does not collect personal data. 
- The GlobalCache service only manges data handed over by other services and components.
- The GlobalCache service is ignorant of any personal data handed over by services and components.

## Data being presented
- The GlobalCache service does not present any personal data.

## Data being deleted
- The GlobalCache service deletes data if manually prompted to do so by services and components or automatically if the time limit on the life time of a data entry has expired. 
- It can be determined in the config.json file of the ILIAS setup whether or not the GlobalCache has the option to delete entries automatically by looking up the used caching option:
  - Caching options with automatic deletion: Apc, Memcache, Xcache
  - Caching options without automatic deletion: Shm, Static Cache

## Data being exported
- The GlobalCache service does not export any personal data.
