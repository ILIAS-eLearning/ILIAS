# Media Pool Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Media Pool component employs the following services, please consult the respective privacy.mds
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [COPage](../COPage/PRIVACY.md)
    - [Media Objects](../MediaObjects/PRIVACY.md)
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Info Screen Service](../InfoScreen/PRIVACY.md)
    - [Metadata](../MetaData/Privacy.md)
    - Custom metadata (aka advanced metadata) can be used to define additional metadata for single media objects or content snippets.

## Configuration

**Global**

- There is no privacy related global configration for media pools. Content snippets can be activated in the media pool administration.

**Media Pool**

- The media pool settings do not include privacy related configurations. The only exception are custom metadata that may be activated. Since custom metadata can be configured in any way, you may define metadata fields to collect personal data. 

## Data being stored

The media pool does not store any privacy related data. Main content of media pools are media objects and content snippets (pages). These features are offered by integrating the components [COPage](../COPage/PRIVACY.md) and [Media Objects](../MediaObjects/PRIVACY.md). Please consult the respective privacy.mds.

## Data being presented

The media pool does not implement any views that offer presentation of personal data, unless custom metadata is defined that holds this kind of data. These fields and their contents are presented in table overviews.

## Data being deleted

- Please consult the deletion processes of media objects and content snippets (pages), see [COPage](../COPage/PRIVACY.md) and [Media Objects](../MediaObjects/PRIVACY.md).
- Deleting the media pool will delete all content snippets and their data. Media objects will only be deleted completely, if they are not used in other content components anymore.

## Data being exported

- No personal data is exported by the media pool export, except the data that is offered by the metadata service.