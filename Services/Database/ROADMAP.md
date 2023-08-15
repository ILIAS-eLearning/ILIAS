# Roadmap


## Short Term

### Put Database under Coordinator-Model
The goal is to put the Database under the Coordinator-Model. The service is crucial for the whole system and should not be managed by a single person.


## Mid Term

### Project: Establish Referential Integrity
Currently (ILIAS 8) ILIAS doesn't use advanced database-built-in functionalities that ensure the integrity of stored data.

The benefits ILIAS demands from the Database Management System (DBMS) regarding data value correctness on field-level currently are:

- uniqueness, by primary or unique indexes
- 'not null' (without a defined default) for essential required fields
- low-level datatype warranty on field-level, reasonable for numeric- and date/time-types, sometimes poor for unspecific varchar fields (examples: email and client_ip in `usr_data`)
- 
To improve data quality and to support code maintaining modern DBMSs offer several options ILIAS COULD use:

- Referential Integrity (Foreign Keys)
- Stored Procedures & Functions
- Trigger

For more information visit the project page: [Project: Establish Referential Integrity](https://docu.ilias.de/goto_docu_wiki_wpage_7319_1357.html) 


## Long Term

### Query-Builder and and ORM 
The goal is to implement a Query-Builder and an ORM or move to a framework which provides these features such as Doctrine.

