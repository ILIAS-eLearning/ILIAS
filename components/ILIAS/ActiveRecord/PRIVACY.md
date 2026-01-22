# ActiveRecord Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Numerous components use the ActiveRecord component for managing (storing, updating and deleting) their data in the database.
- The ActiveRecord component itself is ignorant of the specifics of the data it manages.
- Components which use the ActiveRecord component might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Integrated Services
- The ActiveRecord component employs the following services, please consult the respective privacy.mds
    - [Cache](../../components/ILIAS/Cache/PRIVACY.md)

## Data being stored
- The ActiveRecord component itself does not collect personal data.
- The ActiveRecord component only manages data handed over by other components.
- The ActiveRecord component is ignorant of any personal data handed over by components.

## Data being presented
- The ActiveRecord component does not present any personal data.

## Data being deleted
- The ActiveRecord component deletes data of a component if prompted to do so by said component.
- The ActiveRecord component is ignorant of any personal data being deleted.

## Data being exported
- The ActiveRecord component does not export any personal data.
