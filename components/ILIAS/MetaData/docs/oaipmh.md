# OER Publishing and OAI-PMH Interface

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

Objects in ILIAS under appropriate [copyright licences](copyrights.md) can be published
as OER: they are then referenced in a pre-configured Category such that for
example a public area on an installation can be populated with OER found on
the installation. Additionally, a 'Public Access' export files is generated
for published Objects without one.

Further, an OAI-PMH interface can be activated, with which published
Objects can be queried externally. The information given out contains,
among other useful metadata, static links to the Objects, and download links
for their 'Public Access' export files. OER referatories can then harvest
the published OER previously collected in the public area, and directly link
to the Objects and their exports.

### Supported Object Types

Currently, only the following Objects can be published:

- Blog
- Content Page
- Data Collection
- Exercise
- File
- Glossary
- Learning Module ILIAS
- Learning Module HTML
- Learning Module SCORM
- Mediacast
- Mediapool
- Question Pool Survey
- Question Pool Test
- Weblink
- Wiki

these types can be disabled individually in the Publishing settings
in the Metadata Administration.

## OER Publishing

### Prerequisites and Settings

Publishing of Objects can be activated (either manual, automatic, or both)
and configured in the Metadata Administration. There the Category that OER
are published to can be selected, along with Object types and
[copyright licences](copyrights.md) eligible for publishing. As a prerequisite,
Copyright selection has to be enabled.

Further, an editorial step can be added to the publishing workflow. If
enabled, OER are first linked to in a separate editorial Category, where
it can be checked by an editorial team before publication proper.

Lastly, the [OER Harvester](#oer-harvester-and-automatic-publishing)
cron job must be running for publishing to function properly. It can be
activated in the 'Cron Jobs' Administration.

### Publishing Control Center

If publishing is enabled in the Metadata Administration, even when set to
automatic, the 'Metadata'-tab of Objects with eligible type offers access
to a sort of publishing control center. There, the publishing status of the
Object is shown as follows:

- **Unpublished:** Default status for new objects.
- **Blocked:** Only shown when automatic publishing is enabled. The Object
  will not be automatically published, regardless of the selected copyright licence.
- **Under Review:** Only shown when the editorial step is enabled. The Object
  is waiting for final approval in the editorial Category.
- **Published:** The Object is published as OER. It is referenced in the
  OER Category, a 'Public Access' export file was created for it, and it has
  a pre-compiled record for the OAI-PMH interface.

Further, related actions are offered to the user, according to the
configuration of the publishing workflow:

- **Block:** Only offered for unpublished Objects when automatic publishing
 is enabled. Blocks the Object from publishing.
- **Unblock:** Only offered for blocked Objects when automatic publishing is
  enabled. Reverts the Object's status to unpublished.
- **Publish:** Only offered for unpublished Objects when the editorial step
  is not enabled. Creates a reference to the Object in the previously configured
  OER Category, creates a new export file and flags it as 'Public Access' (if
  there isn't one such export file already), and compiles a record for the
  OAI-PMH interface.<br/>
  'Publish' is disabled if the user does not have permission to create Objects
  of the current Object's type in the OER Category.
- **Withdraw:** Only offered for published Objects, or for Objects under review
  when viewed outside the editorial Category (if the editorial step is enabled).
  Removes the Object's reference from the previously configured OER Category, or
  from the editorial Category as applicable. If a record exists in the OAI-PMH
  interface, it is marked as deleted (see [below](#implementation) for details).
  When automatic publishing is enabled, the Object is also blocked from publishing.<br/>
  'Withdraw' is disabled if the user does not have permission to delete the Object's
  reference from the editorial or OER Category.
- **Submit:** Only offered for unpublished Objects when the editorial step
  is enabled. Creates a reference to the Object in the editorial Category, and
  creates a new export file and flags it as 'Public Access' (if there isn't one
  such export file already).<br/>
  'Submit' is disabled if the user does not have permission to create Objects 
  of the current Object's type in the editorial Category.
- **Accept:** Only offered for unpublished Objects when viewed in the editorial
  Category, when the editorial step is enabled. Moves the Object's reference from
  the editorial to the OER Category, and compiles a record for the OAI-PMH interface.<br/>
  'Publish' is disabled if the user does not have permission to create Objects
  of the current Object's type in the OER Category.<br/>
  'Accept' is disabled if the user does not have permission to create Objects
  of the current Object's type in the OER Category, or to delete the Object's
  reference from the editorial Category.
- **Reject:** Only offered for unpublished Objects when viewed in the editorial
  Category, when the editorial step is enabled. Functions identically to 'Withdraw'.<br/>
  'Reject' is disabled if the user does not have permission to delete the Object's
  reference from the editorial Category.

### OER Harvester and Automatic Publishing

The OER Harvester is a cron job that automatically collects and publishes
(or submits) eligible Objects in the Repository, if automatic publishing is
enabled. It is also responsible for a number of clean-up tasks to make
publishing function properly. It has to be active, even if only manual
publishing is enabled.

On each run, the OER Harvester does the following (see [above](#publishing-control-center)
for details on statuses and actions):

- Objects that are published or under review, but are not eligible for
  publishing anymore, are withdrawn. An Object can become ineligible as
  follows:
  - Either the copyright licence selected for the Object is changed, or the
    selection of eligible licences is changed in the Metadata Administration,
    such that the Object's licence is not eligible anymore.
  - The Object's type is removed from the selection of eligible Object types
    in the Metadata Administration.
  - The Object is deleted from the Repository.
  - The Object's reference in the editorial or OER Category created during
    publishing is deleted.
- The record for the OAI-PMH interface of published Objects is updated, if
  they or their LOM have changed in such a way that the fields defined
  [below](#mapping-of-metadata) change.
- Records for the OAI-PMH interface [marked as deleted](#implementation) are
  removed if they are older than 30 days.
- If automatic publishing is enabled, eligible Objects are published, or
  submitted for review if the editorial step is enabled. An Object is
  eligible if all the following conditions are fulfilled:
  - It must have an eligible copyright licence selected, as configured in
    the Metadata Administration.
  - It must be of an [eligible type](#supported-object-types) as configured
    in the Metadata Administration.
  - It must be in the Repository and not deleted.
  - It must not already be published or under review.

## OAI-PMH Interface

If enabled in the Metadata Administration, records compiled by the OER
Harvester can be queried by external parties via an interface implementing
the [OAI-PMH protocol](https://www.openarchives.org/OAI/openarchivesprotocol.html). The associated endpoint is `{ILIAS base path}/oai.php`.

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
no `about` containers, responses are not compressed is not tracked, and
granularity of datestamps is `YYYY-MM-DD`. Only one trivial set named
'default' is implemented, which always contains all records, in case any
consumers of the interface strictly require a set.

Deleted Records are supported at the level `transient`. Deleted Records
are kept for all resources that are not published anymore for whatever
reason, but only for 30 days.

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
| creator        | `entity` of `lifecycle > contribute` where `role > value` is 'author'                                     | any                   | order of authors is respected                                                                                                                                                                                      |
| subject        | `general > keyword > string`, and `taxonPath` of `classification` where `purpose > value` is 'discipline' | any                   | with corresponding `general > keyword > language` as `xml:lang` attribute, and each `taxonPath` represented by colon-separated `taxon > entry > strings`                                                           |
| description    | `general > description > string`                                                                          | any                   | with corresponding `general > description > language` as `xml:lang` attribute                                                                                                                                      |
| publisher      | `entity` of `lifecycle > contribute` where `role > value` is 'publisher'                                  | any                   | order of publishers is respected                                                                                                                                                                                   |
| contributor    | `entity` of `lifecycle > contribute` where `role > value` is not 'author' or 'publisher'                  | any                   | order of contributors is respected, the role is appended in brackets                                                                                                                                               |
| date           | first `lifecycle > contribute > date`                                                                     | 0 or 1                | in `YYYY-MM-DD` format                                                                                                                                                                                     |
| type           | `educational > learningResourceType > value`                                                              | any                   |                                                                                                                                                                                                                    |
| format         | `technical > format`                                                                                      | any                   |                                                                                                                                                                                                                    |
| identifier     | -                                                                                                         | 1 or 2                | The first identifier always contains static link to the Object in the Category for published OER. If the Object has a 'Public Access' export file, a download link to the file is included in a second identifier. |
| source         | `resource > identifier > entry` of `relation` where `kind > value` is 'isbasedon'                         | any                   |                                                                                                                                                                                                                    |
| relation       | `resource > identifier > entry` of `relation` where `kind > value` is not 'isbasedon'                     | any                   |                                                                                                                                                                                                                    |
| coverage       | `general > coverage > string`                                                                             | any                   | with corresponding `general > coverage > language` as `xml:lang` attribute                                                                                                                                         |
| rights         | `rights > description > string`                                                                           | 1                     | If a [pre-configured license](copyrights.md) is chosen for the Object, it is given by its link (or its full name, if it does not have a link). If no copyright is set, the default copyright is used.              |
