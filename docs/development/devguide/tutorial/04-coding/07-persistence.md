# Persistence in ILIAS

The tutorials on persistence describe how you can store information in ILIAS for the long term. This is necessary for almost every application-related functionality and ILIAS offers two basic services for this purpose:

* The [database service](../../../../../components/ILIAS/Database/README.md) allows you to create SQL database tables, store data in them and query them. Database tables are used to store small pieces of information that are entered directly or generated automatically when using ILIAS. They are suitable for quick access, good searchability and frequent changes.

* The [Integrated Resource Storage Service](../../../../../components/ILIAS/ResourceStorage/README.md) is used for the standardised storage of files that are uploaded to ILIAS and are to be further processed or delivered as such. These can be documents or media, for example. Once saved, you can only access such files via their IDs. The service can save a resource in different versions (revisions) and variants (flavours) with associated basic data (information) such as type, size and title. If you want to save further information about a file, store it with the ID in a separate database table. Resources can be bundled in groups (collections) and sorted and queried in sections, e.g. for listing in tables.


The [tutorial for database usage](./08-persistence-data.md) guides you through the creation and use of a database table using the example of a to-do list.
