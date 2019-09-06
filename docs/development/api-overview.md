# API and Services Overview
We are currently revising lots of libraries, services and APIs in ILIAS. This overview contains also links to legacy or deprecated services if the are still being used in the core ILIAS code and subject to ongoing refactorings.

This list does not contain information on third party libraries being used. You will find these in the [libs directory](../../../libs/README.md).


## Core Libs

ILIAS core libs are located in the src folder. There are [special guidelines](../../../src/README.md) for contributions to these libs.

- [DI](../../src/DI/README.md): Dependency Injection Container
- [Data](../../src/Data/README.md): Standard Data Types
- [Refinery](../../src/Refinery/README.md): Input and Data Processing
- [HTTP](../../src/HTTP/README.md): PSR-7 HTTP Request and Response Handling
- [Filesystem](../../src/Filesystem/README.md): Filesystem Access
- [FileUpload](../../src/FileUpload/README.md): File Upload Handling
- [ArtifactBuilder](../../src/ArtifactBuilder/README.md): Artifact Builder
- [BackgroundTasks](../../src/BackgroundTasks/README.md): Background Task Management
- [UI](../../src/UI/README.md): User Interface Framework
- [GlobalScreen](../../src/GlobalScreen/README.md): Layout Mediator
- [KioskMode](../../src/KioskMode/README.md): Kiosk Mode


## Services

Services provide general functionalities used in the modules or in other services, e.g. the role based access system or the news system.

**Persistence**

- [Database](../../Services/Database/README.md): Database Access
- [ActiveRecord](../../Services/ActiveRecord/README.md): Active Record

**Processing**

- [Cron](../../Services/Cron/README.md): Cron Job Management

**Objects and Repository**

- [Object](../../Services/Object/README.md): Objects Service
- [Conditions](../../Services/Conditions/README.md): Pre-Conditions for Repository Objects

**Content and Output**

- [Template Engine](../../Services/UICore/template-engine.md): Core Template Engine
- [UI Controller](../../Services/UICore/ilctrl.md): User Interface Control Flow Management
- [Legacy UI](https://docu.ilias.de/goto_docu_st_64268_42.html): Beside the current [UI framework](../../src/UI/README.md) you will still find some legacy UI components in ILIAS code. Their documentation is still available in the development guide.
- [COPage](../../Services/COPage/README.md): Page Content Editor
- [AdvancedMetadata](../../Services/AdvancedMetaData/README.md): Advanced Metadata
- [Excel](../../Services/Excel/README.md): Spreadsheet Service

**Communication and Information**

- [Mail](../../Services/Mail/README.md): Mail Service
- [News](../../Services/News/README.md): News Service
- [Task](../../Services/Tasks/README.md): (Derived) Tasks Service
- [Like](../../Services/Like/README.md): Like Service

**Learning Outcomes**

- [Certificate](../../Services/Certificate/README.md): Certificate Management
- [LearningHistory](../../Services/LearningHistory/README.md): Learning History


## Domain APIs

[WIP]
