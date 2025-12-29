# API and Services Overview

We are currently revising lots of libraries, services and APIs in ILIAS. This overview contains also links to legacy or deprecated services if they are still being used in the core ILIAS code and subject to ongoing refactorings.

This list does not contain information on third party libraries being used. You will find these in the [vendor directory](../../vendor/README.md).


## Core Libs

ILIAS core libs are located in the 'components' folder. There are [old guidelines](component-rules.md) and [new guidelines](components-and-directories.md)  for contributions to these libs.

- [DI](../../components/ILIAS/DI/README.md): Dependency Injection Container
- [Data](../../components/ILIAS/Data/README.md): Standard Data Types
- [Refinery](../../components/ILIAS/Refinery/README.md): Input and Data Processing
- [HTTP](../../components/ILIAS/HTTP/README.md): PSR-7 HTTP Request and Response Handling
- [Filesystem](../../components/ILIAS/Filesystem/README.md): Filesystem Access
- [FileUpload](../../components/ILIAS/FileUpload/README.md): File Upload Handling
- [ResourceStorage](../../components/ILIAS/ResourceStorage/README.md): Store and manage resources (e.g. uploaded files)
- [BackgroundTasks](../../components/ILIAS/BackgroundTasks/README.md): Background Task Management
- [UI](../../components/ILIAS/UI/README.md): User Interface Framework
- [GlobalScreen](../../components/ILIAS/GlobalScreen/README.md): Layout Mediator
- [KioskMode](../../components/ILIAS/KioskMode/README.md): Kiosk Mode
- [Setup](../../components/ILIAS/Setup/README.md): Mechanics for the Setup, contains the ArtifactBuilder


## Services

Services provide general functionalities used in the modules or in other services, e.g. the role based access system or the news system.

**Persistence**

- [Database](../../components/ILIAS/Database/README.md): Database Access
- [ActiveRecord](../../components/ILIAS/ActiveRecord/README.md): Active Record

**Processing**

- [Cron](../../components/ILIAS/Cron/README.md): Cron Job Management

**Objects and Repository**

- [Object](../../components/ILIAS/ILIASObject/README.md): Objects Service
- [Conditions](../../components/ILIAS/Conditions/README.md): Pre-Conditions for Repository Objects

**Content and Output**

- [Template Engine](../../components/ILIAS/UICore/template-engine.md): Core Template Engine
- [UI Controller](../../components/ILIAS/UICore/ilctrl.md): User Interface Control Flow Management
- [Legacy UI](https://docu.ilias.de/goto_docu_st_64268_42.html): Beside the current [UI framework](../../components/ILIAS/UI/README.md) you will still find some legacy UI components in ILIAS code. Their documentation is still available in the development guide.
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
