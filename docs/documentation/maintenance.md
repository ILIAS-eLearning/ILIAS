ILIAS Maintenance
=================
The development of the ILIAS source code is coordinated and maintained by a coordination team within the ILIAS network. Besides the main responsibilities for the project, several developers and users are maintaining certain modules of ILIAS.

# Coordination Team

* **Product Management**: [Matthias Kunkel]
* **Technical Board**: [Alexander Killing], [Michael Jansen], [Fabian Schmid], [Timon Amstutz], [Richard Klees]
* **Testcase Management**: [Fabian Kruse]
* **Documentation**: [Florian Suittenpointner]
* **Online Help**: [Alexandra Tödt]

# Maintainers
We highly appreciate to get new developers but we have to guarantee the sustainability and the quality of the ILIAS source code. The system is complex for new developers and they need to know the concepts of ILIAS that are described in the development guide.
 
Communication among developers that are working on a specific module needs to be assured. Final decision about getting write access to the ILIAS development system (Github) is handled by the product manager.
 
The following rules must be respected for everyone involved in the programming of ILIAS:

1. Decisions on new features or feature removals are made by the responsible first maintainer and the product manager in the Jour Fixe meetings after an open discussion.
2. All components have a first and second maintainer. Code changes are usually done by the first maintainer. The first maintainer may forward new implementations to the second maintainer.

Responsibilities of a component maintainer:

- Component maintainer must assure maintenance of their component for at least three years (approx. three ILIAS major releases).
- Component maintainers must agree to coordinate the development of their component with the product manager.
- Component maintainer are responsible for bug fixing of their component and get assigned related bugs automatically by the [Issue-Tracker](http://mantis.ilias.de).

ILIAS is currently maintained by two types of Maintainerships:

- First Maintainer
- Second Maintainer

The code base is deviced in several components:
<!-- REMOVE -->
* **UI-Service**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: src/UI, 
* **BackgroundTasks**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/BackgroundTask, 
* **ActiveRecord**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/ActiveRecord, 
* **Shibboleth Authentication**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
	* Used in Directories: Services/AuthShibboleth, 
* **Badges**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **GlobalCache**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/ActiveRecord, 
* **Cloud Object**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Used in Directories: Modules/Cloud, 
* **Plugin Slots**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: MISSING
	* Tester: [PerPascalGrube](http://www.ilias.de/docu/goto_docu_usr_31492.html)
	* Used in Directories: Services/Component, 
* **Initialisation**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, 
* **Booking Manager**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Database**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/Database, 
* **ObjectDefinition**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **ItemGroup**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/ActiveRecord, 
* **Notes and Comments**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Used in Directories: Services/Notes, 
* **User Service**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **EventHandling**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, 
* **Object**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, 
* **Media Objects**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Excel**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/FileDelivery, 
* **Component**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, 
* **Style**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/FileDelivery, 
* **Booking Tool**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Modules/BookingManager, 
* **ilUtil**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/FileDelivery, 
* **Learning Module SCORM**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Tagging**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Used in Directories: Services/Tagging, 
* **TemplateEngine**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, 
* **Certificate**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Chat**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Learning Module HTML**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Poll**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Portfolio**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Test**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, 
* **Organisational Units**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
	* Tester: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
	* Used in Directories: Services/ActiveRecord, Modules/OrgUnit, 
* **Web Access Checker**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: iLUB Universität Bern
	* Used in Directories: Services/ActiveRecord, Services/FileDelivery, 
* **MediaCast**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Used in Directories: Services/WebAccessChecker, Modules/MediaCast, 
* **Blog**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/WebAccessChecker, Modules/Blog, 
* **Language**
	* 1st Maintainer: MISSING
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING
	* Used in Directories: Services/GlobalCache, Services/Language, 
* **Study Programme**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](http://www.ilias.de/docu/goto_docu_usr_45419.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Used in Directories: Services/ActiveRecord, Modules/StudyProgramme, 
* **File**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Testcases: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)
	* Tester: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)
	* Used in Directories: Services/FileDelivery, Services/WebAccessChecker, Modules/File, 
* **Data Collection**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [kim.schmidt](http://www.ilias.de/docu/goto_docu_usr_28720.html)
	* Used in Directories: Services/ActiveRecord, Services/WebAccessChecker, Modules/DataCollection, 
* **Bibliographic List Item**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [marko.glaubitz](http://www.ilias.de/docu/goto_docu_usr_28309.html)
	* Used in Directories: Services/ActiveRecord, Services/FileDelivery, Modules/Bibliographic, 

Components in the Service-Maintenance-Model:


The following directories are currently maintained unter the Classic-Maintenace-Model:
* Modules/Blog
 (1st Maintainer: MISSING)
* Services/Notes
 (1st Maintainer: MISSING)
* Services/Language
 (1st Maintainer: MISSING)
* Modules/MediaCast
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html))
* Services/Tagging
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html))
* Services/BackgroundTask
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileDelivery
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/GlobalCache
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/AuthShibboleth
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/ActiveRecord
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Bibliographic
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/DataCollection
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/File
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Cloud
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/OrgUnit
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/WebAccessChecker
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/StudyProgramme
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))


The following directories are currently maintained unter the Service-Maintenace-Model:
* Services/Database
 (Coordinator: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* src/UI
 (Coordinator: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html))


The following directories are currently unmaintained:
* Modules/BookingManager
* Modules/Category
* Modules/CategoryReference
* Modules/Chatroom
* Modules/Course
* Modules/CourseReference
* Modules/Exercise
* Modules/ExternalFeed
* Modules/Folder
* Modules/Forum
* Modules/Glossary
* Modules/Group
* Modules/GroupReference
* Modules/HTMLLearningModule
* Modules/IndividualAssessment
* Modules/ItemGroup
* Modules/LearningModule
* Modules/MediaPool
* Modules/Poll
* Modules/Portfolio
* Modules/RemoteCategory
* Modules/RemoteCourse
* Modules/RemoteFile
* Modules/RemoteGlossary
* Modules/RemoteGroup
* Modules/RemoteLearningModule
* Modules/RemoteTest
* Modules/RemoteWiki
* Modules/RootFolder
* Modules/Scorm2004
* Modules/ScormAicc
* Modules/Session
* Modules/Survey
* Modules/SurveyQuestionPool
* Modules/SystemFolder
* Modules/Test
* Modules/TestQuestionPool
* Modules/WebResource
* Modules/Wiki
* Modules/WorkspaceFolder
* Modules/WorkspaceRootFolder
* Services/ADT
* Services/AccessControl
* Services/Accessibility
* Services/Accordion
* Services/Administration
* Services/AdvancedEditing
* Services/AdvancedMetaData
* Services/AuthApache
* Services/Authentication
* Services/Awareness
* Services/Badge
* Services/Block
* Services/Booking
* Services/Bookmarks
* Services/CAS
* Services/COPage
* Services/Cache
* Services/Calendar
* Services/Captcha
* Services/Certificate
* Services/Chart
* Services/Classification
* Services/Clipboard
* Services/Component
* Services/Contact
* Services/Container
* Services/ContainerReference
* Services/Context
* Services/CopyWizard
* Services/Cron
* Services/DataSet
* Services/DidacticTemplate
* Services/DiskQuota
* Services/Dom
* Services/Environment
* Services/EventHandling
* Services/Excel
* Services/Exceptions
* Services/Export
* Services/Feeds
* Services/FileSystem
* Services/FileUpload
* Services/Form
* Services/Frameset
* Services/Help
* Services/History
* Services/Html
* Services/Http
* Services/Imprint
* Services/InfoScreen
* Services/Init
* Services/JSON
* Services/JavaScript
* Services/LDAP
* Services/License
* Services/Link
* Services/LinkChecker
* Services/Locator
* Services/Logging
* Services/Mail
* Services/MainMenu
* Services/Maps
* Services/Math
* Services/MathJax
* Services/MediaObjects
* Services/Membership
* Services/MetaData
* Services/Migration
* Services/Multilingualism
* Services/Navigation
* Services/News
* Services/Notification
* Services/Notifications
* Services/Object
* Services/OnScreenChat
* Services/PDFGeneration
* Services/PHPUnit
* Services/Password
* Services/PermanentLink
* Services/PersonalDesktop
* Services/PersonalWorkspace
* Services/Preview
* Services/PrivacySecurity
* Services/QTI
* Services/RTE
* Services/Radius
* Services/Randomization
* Services/Rating
* Services/Registration
* Services/Repository
* Services/SOAPAuth
* Services/Search
* Services/Skill
* Services/Style
* Services/Survey
* Services/SystemCheck
* Services/Table
* Services/Taxonomy
* Services/TermsOfService
* Services/Tracking
* Services/Transformation
* Services/Tree
* Services/UIComponent
* Services/UICore
* Services/User
* Services/Utilities
* Services/Verification
* Services/VirusScanner
* Services/WebDAV
* Services/WebServices
* Services/WorkflowEngine
* Services/XHTMLPage
* Services/XHTMLValidator
* Services/Xml
* Services/YUI
* Services/jQuery
* src/DI
