# Mediacast Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Mediacast Module component employs the following services, please consult the respective privacy.mds
    - The **Learning Progress** service manages data on access time specifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - [News Service](../../Services/News/Privacy.md)

## General Information

- The **[News Service](../../Services/News/Privacy.md)** service is the foundation of the mediacast. A mediacast entry is a news item with an attached media object.

## Configuration

- **Global**
    - The global mediacast administration allows to set the default access for mediacast entries. If set to **public**, the **RSS** representation will be accessible **without authentication**, see [News Service](../../Services/News/Privacy.md) service.
- **Mediacast**
    - The mediacast settings allow to (de-)activate the RSS feed and to overwrite the default access (**public RSS** on/off).
- **Mediacast Entry**
    - Each entry has an access setting (**public RSS** on/off).

## Data being stored

The mediacast does not store personal data itself. Personal data is only stored by the integrated services.

## Data being presented

The presentation of mediacast items itself does not include personal data in general (e.g. the creator/author is not displayed). Personal information may only be part of the mediacast content itself (e.g. in vidoes or audio files).

**Learner Presentation** (Read Permission)
- For each item a **creation and update timestamp** is presented in the list view. But without information on the creator/updater.
- The same **timestamps** are part of the **RSS feed**, again without personal data of the creator/updater.

**Tutor Presentation** (Edit Settings Permission)
- Additionally to the learner presentation the tutor presentation lists a "played" counter, but without the information which user has played an item.

**Tutor Presentation** (Edit Learning Progress Permission)
- If the learning progress is activated in mode "Collection of Media Objects", tutors see, which item has been "completed" by learners.

## Data being deleted

- If a **mediacast** is deleted
    - the corresponding news entries and their data are deleted, too.

## Data being exported

- XML Exports of Mediacasts do not contain any personal data.
