# Media Objects Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Media Object Service component employs the following services, please consult the respective privacy.mds
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - The **User** service provides a "Personal Clipboard" feature. It is possible to store media objects here. A user/media object relation will be stored by this service.

## Configuration

There is no privacy related configuration for media objects.

## Data being stored

The media objects do not store personal data themselves. Personal data is only stored by the integrated services.

## Data being presented

The presentation of media objects does not include personal data in general. However the integrated metadata service presents e.g. data on authors.

## Data being deleted

- If a **media object** is deleted
    - the attached metadata is deleted, too.

## Data being exported

- XML Exports of Media Objects do not contain any personal data. The included metadata however may include author information and similar data.
