# OER Harvester and OAI-PMH Interface

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

The OER Harvester is a cron job that can find and collect Objects under
appropriate [copyright licences](copyrights.md) in the Repository of the installation.
These objects are referenced in a pre-configured Category such that for
example a public area on an installation can automatically be populated
with OER found on the installation. Additionally, the Harvester automatically
generates 'Public Access' export files for harvested Objects without one.

Further, an OAI-PMH interface can be activated, with which appropriate Objects
in a Category can be queried externally. The information given out contains,
among other useful metadata, static links to the Objects, and download links
for their 'Public Access' export files. For example, OER referatories can
then harvest the OER previously collected in the public area, and directly link
to the Objects and their exports.

### Supported Object Types

Currently, the OER Harvester and OAI-PMH interface only work with the
following objects:

- File
- Glossary
- Content Page
- Learning Module ILIAS
- Learning Module SCORM
- Learning Module HTML
- Question Pool Survey
- Question Pool Test
- Mediapool

In the OER Harvester cron job configuration, these types can be disabled
individually.

## OER Harvester

### Prerequisites and Settings

The OER Harvester needs to be activated in the 'Cron Jobs' Administration.
For it to actually harvest Objects, copyright selection has to be enabled in
the 'Metadata' Administration. In the cron job configuration of the Harvester,
to-be-collected licences then need to be chosen, along with Categories
for the harvested and published OER (see below for details). Further,
supported object types can be disabled indivdiually.

Disabling the copyright selection again after Objects have already had
licences assigned is not recommended, and can lead to unpredictable results
when running the OER Harvester.

### Harvesting

When executed, the OER Harvester will search the installation for eligible
Objects. All Objects found are then referenced in the pre-configured Category
for harvested OER. In addition, if the Objects do not have an export file flagged
as 'Public Access', a new export file is automatically generated for
them, and set to 'Public Access'.

The following conditions must be fulfilled for an Object to be harvested:

- The Object must be in the Repository, and it must not be in the
  Trash (or deleted entirely).
- The Object must be of a supported type, and that type must not be
  disabled in the Harvester's cron job configuration.
- In the Object's LOM, the copyright must be one of those selected in the
  Harvester's cron job configuration.
- The Object must not be blocked from being harvested (see 
  [below](#blocking-individual-objects)).
- The Object must not already be harvested. Objects whose reference was 
  manually deleted from the Category for harvested OER still count as harvested.
- In the Harvester's cron job configuration, a Category for harvested OER
  must be set.

If any of these conditions except the last two are not fulfilled any more for a
previously harvested Object, the Harvester deletes its reference from the
Category for harvested OER. It does however not also deleted automatically
generated export files. The Object then does not count as harvested
any more, and could thus be harvested again in the future. Only Objects
unflagged in this way can be harvested again.

### Blocking Individual Objects

Users with access to an Object's 'Metadata'-tab can block the Object from
being harvested. The option is offered when choosing an eligible copyright
licence for an Object with a type supported by the Harvester (and not
disabled in the configuration).

Note that when an Object's copyright is changed in the 'Metadata'-tab (but
not in the full LOM editor), and the option to block an object from
being harvested is offered but not taken, the user is notified about the
Object potentially being published, and asked for confirmation.

Blocking an already harvested Object will lead to its reference in the
Category for harvested OER being deleted when the Harvester is executed next.

### Exposed Records

In addition to harvesting OER, the Harvester also complies records of OER
to be exposed for querying through the OAI-PMH interface. A record is
compiled for an Object if the following conditions are fulfilled:

- The Object must be in the Repository, and it must not be in the
  Trash (or deleted entirely).
- The Object must be of a supported type, and that type must not be
  disabled in the Harvester's cron job configuration.
- In the Object's LOM, the copyright must be one of those selected in the
  Harvester's cron job configuration.
- The Object must not be blocked from being harvested (see
  [below](#blocking-individual-objects)).
- The Object must have a reference in the Category for published OER.
  That reference can also be in a (nested) Subobject of the Category.
- In the Harvester's cron job configuration, a Category for published OER
  must be set.

Note that most of these conditions match those [for harvesting](#harvesting),
such that for the most part records are compiled for Objects that were
previously harvested, although this is not technically a prerequisite.

If any of these conditions are not fulfilled any more for a previously exposed
Object, the Object's record will be deleted, and it will not be
available through the OAI-PMH interface on future requests.

If changes were made to the LOM of a previously exposed object, its record
will be updated accordingly if necessary (see [below](#mapping-of-metadata)
for details), including the date of change.

#### Automatic Publishing

The OER Harvester can be configured in such a way that for every harvested
Object, a record is compiled immediately. Then OER on the installation are
effectively published automatically through the OAI-PMH interface, as soon as an
eligible copyright is chosen for them. If that is the desired behaviour, choose
the same Category for harvested and published OER in the Harvester's cron
job configuration.

If on the other hand OER should not be published automatically, for example
for quality control, two different Categories should be selected. In this case,
Objects must be moved (or linked) manually from the Category for harvested OER to that for
published OER.

## OAI-PMH Interface

If enabled in the Metadata Administration, records compiled by the OER
Harvester can be queried by external parties via an interface implementing
the [OAI-PMH protocol](https://www.openarchives.org/OAI/openarchivesprotocol.html).
The associated endpoint is `{ILIAS base path}/oai.php`.

Note that records of OER in responses contain static links
to the associated Objects in the Category for published OER. For exposed
Objects to be available for interested external users, that Category should
thus be in the public area of the installation.

### Prerequisites and Settings

The interface has to be enabled in the Metadata Administration. Additionally,
some information has to be filled out there to determine how the installation
should identify itself via the interface:

- **Repository Name and Contact E-Mail** are used in responses to
  `Identify`-requests to the interface.
- **OAI Prefix** is used as a namespace for the identifiers of records.
  The identifiers are generated as `{prefix}il__{Object Type}_{Object ID}`.

While it is technically possible to enable the interface but not the OER
Harvester, no records will be returned without it. It is also recommended
to keep the Harvester enabled while the interface is, such that records are 
always kept up to date.

### Implementation

The implementation of the OAI-PMH interface is for the most part minimal, as specified 
[here](https://www.openarchives.org/OAI/2.0/guidelines-repository.htm#MinimalImplementation). The only supported metadata format is Simple DC, there are
no `about` containers, responses are not compressed, deletion of records
is not tracked, and granularity of datestamps is `YYYY-MM-DD`. Only one
trivial set named 'default' is implemented, which always contains all
records, in case any consumers of the interface strictly require a set.

The interface will however return resumption tokens for lists with more
than 100 entries. The state of the request is encoded in the token, nothing
is cached.

### Mapping of Metadata

Records returned by the interface are compiled by the OER Harvester from
Objects and their LOM. The Simple DC metadata elements in these records
are derived as follows:

| **DC Element** | **From LOM Element(s)**                                                                                   | **No. of Occurences** | **Additional Information**                                                                                                                                                                                         |
|----------------|-----------------------------------------------------------------------------------------------------------|-----------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| title          | `general > title > string`                                                                                | 1                     | with `general > title > language` as `xml:lang` attribute                                                                                                                                                          |
| creator        | `entity` of `lifecycle > contribute` where `role > value` is 'author'                                     | any                   | order of authors will be respected                                                                                                                                                                                 |
| subject        | `general > keyword > string`, and `taxonPath` of `classification` where `purpose > value` is 'discipline' | any                   | with corresponding `general > keyword > language` as `xml:lang` attribute, and each `taxonPath` represented by colon-separated `taxon > entry > strings`                                                           |
| description    | `general > description > string`                                                                          | any                   | with corresponding `general > description > language` as `xml:lang` attribute                                                                                                                                      |
| publisher      | `entity` of `lifecycle > contribute` where `role > value` is 'publisher'                                  | any                   | order of publishers will be respected                                                                                                                                                                              |
| contributor    | `entity` of `lifecycle > contribute` where `role > value` is not 'author' or 'publisher'                  | any                   | order of contributors will be respected                                                                                                                                                                            |
| date           | first `lifecycle > contribute > date`                                                                     | 0 or 1                | will be in `YYYY-MM-DD` format                                                                                                                                                                                     |
| type           | `educational > learningResourceType > value`                                                              | any                   |                                                                                                                                                                                                                    |
| format         | `technical > format`                                                                                      | any                   |                                                                                                                                                                                                                    |
| identifier     | -                                                                                                         | 1 or 2                | The first identifier always contains static link to the Object in the Category for published OER. If the Object has a 'Public Access' export file, a download link to the file is included in a second identifier. |
| source         | `resource > identifier > entry` of `relation` where `kind > value` is 'isbasedon'                         | any                   |                                                                                                                                                                                                                    |
| relation       | `resource > identifier > entry` of `relation` where `kind > value` is not 'isbasedon'                     | any                   |                                                                                                                                                                                                                    |
| coverage       | `general > coverage > string`                                                                             | any                   | with corresponding `general > coverage > language` as `xml:lang` attribute                                                                                                                                         |
| rights         | `rights > description > string`                                                                           | 1                     | If a [pre-configured license](copyrights.md) is chosen for the Object, it is given by its full name and link.                                                                                                      |
