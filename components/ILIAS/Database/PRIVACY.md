# Database Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Components use the Database component for writing data to the ILIAS database as well as reading or deleting data from the ILIAS database.
- Components which use the Database component might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Data being stored
- The Database component itself does not collect any personal data.
- The Database component only manges data handed over by other components.
- The Database component is ignorant of any personal data handed over by components.

## Data being presented
- The Database component does not present any personal data.

## Data being deleted
- The Database component deletes data if manually prompted to do so by components.

## Data being exported
- The Database component does not export any personal data.
