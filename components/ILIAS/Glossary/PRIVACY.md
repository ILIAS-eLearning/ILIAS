# Glossary Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Glossary Module component employs the following services, please consult the respective privacy.mds
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - [COPage](../../Services/COPage/PRIVACY.md)


## Configuration

Currently there are no personal data related settings in the administration or in the glossaries itself.

## Data being stored

For each term two timestamps are being stored: The **creation** and the **last update timestamp**. The glossary does **not** store, which user has created or updated a term.

## Data being presented

Besides from the integrated services, the presentation of the glossary does not present any personal data.

## Data being deleted

- Glossary term timestamps are deleted, when the glossary terms are or the whole glossary is (finally) deleted.

## Data being exported

- XML Exports of Glossaries do not contain any personal data.