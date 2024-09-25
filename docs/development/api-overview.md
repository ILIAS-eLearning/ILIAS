# API and Services Overview

We are currently revising lots of libraries, services and APIs in ILIAS. This overview contains also links to legacy or deprecated services if the are still being used in the core ILIAS code and subject to ongoing refactorings.

This list does not contain information on third party libraries being used. You will find these in the [libs directory](../../libs/README.md).


## Core Libs

ILIAS core libs are located in the src folder. There are [special guidelines](../../src/README.md) for contributions to these libs.

- [DI](../../src/DI/README.md): Dependency Injection Container
- [Data](../../src/Data/README.md): Standard Data Types
- [Refinery](../../src/Refinery/README.md): Input and Data Processing
- [HTTP](../../src/HTTP/README.md): PSR-7 HTTP Request and Response Handling
- [Filesystem](../../src/Filesystem/README.md): Filesystem Access
- [FileUpload](../../src/FileUpload/README.md): File Upload Handling
- [ResourceStorage](../../src/ResourceStorage/README.md): Store and manage resources (e.g. uploaded files)
- [BackgroundTasks](../../src/BackgroundTasks/README.md): Background Task Management
- [UI](../../src/UI/README.md): User Interface Framework
- [GlobalScreen](../../src/GlobalScreen/README.md): Layout Mediator
- [KioskMode](../../src/KioskMode/README.md): Kiosk Mode
- [Setup](../../src/Setup/README.md): Mechanics for the Setup, contains the ArtifactBuilder


## Services

Services provide general functionalities used in the modules or in other services, e.g. the role based access system or the news system.

**Persistence**

- [Database](../../components/ILIAS/Database/README.md): Database Access
- [ActiveRecord](../../components/ILIAS/ActiveRecord/README.md): Active Record

**Processing**

- [Cron](../../components/ILIAS/Cron/README.md): Cron Job Management

**Objects and Repository**

- [Object](../../components/ILIAS/Object/README.md): Objects Service
- [Conditions](../../components/ILIAS/Conditions/README.md): Pre-Conditions for Repository Objects

**Content and Output**

- [Template Engine](../../components/ILIAS/UICore/template-engine.md): Core Template Engine
- [UI Controller](../../components/ILIAS/UICore/ilctrl.md): User Interface Control Flow Management
- [Legacy UI](https://docu.ilias.de/goto_docu_st_64268_42.html): Beside the current [UI framework](../../src/UI/README.md) you will still find some legacy UI components in ILIAS code. Their documentation is still available in the development guide.
- [COPage](../../components/ILIAS/COPage/README.md): Page Content Editor
- [AdvancedMetadata](../../components/ILIAS/AdvancedMetaData/README.md): Advanced Metadata
- [Excel](../../components/ILIAS/Excel/README.md): Spreadsheet Service

**Communication and Information**

- [Mail](../../components/ILIAS/Mail/README.md): Mail Service
- [News](../../components/ILIAS/News/README.md): News Service
- [Task](../../components/ILIAS/Tasks/README.md): (Derived) Tasks Service
- [Like](../../components/ILIAS/Like/README.md): Like Service

**Learning Outcomes**

- [Certificate](../../components/ILIAS/Certificate/README.md): Certificate Management
- [LearningHistory](../../components/ILIAS/LearningHistory/README.md): Learning History
- [Skill](../../components/ILIAS/Skill/README.md#api): Competence Management


## Domain APIs

[WIP]
