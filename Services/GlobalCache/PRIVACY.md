# GlobalCache Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
Services and components use the GlobalCache service for writing data to a cache as well as reading or deleting data from a cache. There are several caching options whose distinctions will be outlined in the following sections if they are relevant to matters of privacy. Services and components which use the GlobalCache service might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.


## Services being used
- The GlobalCache service does not employ any services.

## Data being stored
- The GlobalCache service stores data provided by other services and components.
- As the GlobalCache service only manges data handed over by other services and components without any further information, it cannot be determined if this data is of personal nature. 

## Data being presented
- The GlobalCache service does not present any personal data.

## Data being deleted
- The GlobalCache service deletes data if prompted to do so by services and components which use the GlobalScreen service.
- If a time limit on the life time of a data entry has been set when writing the entry into the cache, several caching options will delete the data after said time limit has expired.
  - Caching options which will delete the entry after the time limit: Apc, Memcache, Xcache
  - Caching options which will not delete the entry after the time limit: Shm, Static Cache

## Data being exported
- The GlobalCache service does not export any personal data.