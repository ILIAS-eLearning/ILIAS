# API Overview
We are currently revising lots of libraries, services and APIs in ILIAS. This overview contains also links to legacy or deprecated services if the are still being used in the core ILIAS code and subject to ongoing refactorings.

This list does not contain information on third party libraries being used. You will find these in the [libs directory](../../../libs/README.md).


## Core Libs

ILIAS core libs are located in the src folder. There are [special guidelines](../../../src/README.md) for contributions to these libs.

- [DI](../../../src/DI/README.md): Dependency Injection Container
- [Data](../../../src/Data/README.md): Standard Data Types
- [Refinery](../../../src/Refinery/README.md): Input and Data Processing
- [HTTP](../../../src/HTTP/README.md): PSR-7 HTTP Request and Response Handling
- [Filesystem](../../../src/Filesystem/README.md): Filesystem Access
- [FileUpload](../../../src/FileUpload/README.md): File Upload Handling
- [BackgroundTasks](../../../src/BackgroundTasks/README.md): Background Task Management
- [UI](../../../src/UI/README.md): User Interface Framework
- [GlobalScreen](../../../src/GlobalScreen/README.md): Layout Mediator
- [KioskMode](../../../src/KioskMode/README.md): Kiosk Mode


## Services

Services provide general functionalities used in the modules or in other services, e.g. the role based access system or the news system.

- [ActiveRecord](../../../Services/ActiveRecord/README.md): Active Record
- [AdvancedMetadata](../../../Services/AdvancedMetadata/README.md): Advanced Metadata
- [Certificate](../../../Services/Certificate/README.md): Certificate Management
- [Conditions](../../../Services/Conditions/README.md): Pre-Conditions for Repository Objects
- [COPage](../../../Services/COPage/README.md): Page Content Editor
- [Cron](../../../Services/Cron/README.md): Cron Job Management
- [Database](../../../Services/Database/README.md): Database Access
- [Excel](../../../Services/Excel/README.md): Spreadsheet Service
- [LearningHistory](../../../Services/LearningHistory/README.md): Learning History
- [Mail](../../../Services/Mail/README.md): Mail Service
- [News](../../../Services/News/README.md): News Service
- [Object](../../../Services/Object/README.md): Objects Service
- [Task](../../../Services/Task/README.md): (Derived) Tasks Service


## Domain APIs

[WIP]
