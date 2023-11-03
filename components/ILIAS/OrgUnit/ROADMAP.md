# Roadmap for Modules/OrgUnit
* Change Positions/Authorities repositories to adapt them to the structure of the other ones
* Refactor PathStorage and remove ActiveRecord
* Combine some of the new repositories to "meta" repositories and provide methods for different use cases (while identifying said use cases in other modules/services)
* Identify and remove deprecated/unused classes
* Refactor other parts of OrgUnit according to Repository Pattern (if applicable)
* Cleanup some code parts and check compatibility for PHP8.1