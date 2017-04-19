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
* **Shibboleth Authentication**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **BackgroundTasks**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **ActiveRecord**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **Administration**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **Glossary**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)

* **GlobalCache**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **Plugin Slots**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: MISSING
	* Tester: [PerPascalGrube](http://www.ilias.de/docu/goto_docu_usr_31492.html)

* **Forum**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](http://www.ilias.de/docu/goto_docu_usr_14206.html)
	* Testcases: FH Aachen
	* Tester: [e.paulmann](http://www.ilias.de/docu/goto_docu_usr_8645.html)

* **ILIAS Page Editor**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: FH Aachen

* **Learning Module ILIAS**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)

* **Course Management**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **Badges**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)

* **Info Page**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: MISSING
	* Tester: MISSING

* **Chat**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [AntoniaWeber](http://www.ilias.de/docu/goto_docu_usr_36934.html)

* **Learning Module HTML**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)
	* Tester: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)

* **Booking Tool**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [e.coroian](http://www.ilias.de/docu/goto_docu_usr_37215.html)
	* Tester: [wolfganghuebsch](http://www.ilias.de/docu/goto_docu_usr_18455.html)

* **Cloud Object**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)

* **Database**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **Tagging**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)

* **CSS / Templates**
	* 1st Maintainer: [braun](http://www.ilias.de/docu/goto_docu_usr_27123.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)

* **Notes and Comments**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)

* **MathJax**
	* 1st Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* 2nd Maintainer: MISSING
	* Testcases: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* Tester: MISSING

* **MediaCast**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)

* **Certificate**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: [christian.hueser](http://www.ilias.de/docu/goto_docu_usr_41129.html)

* **Web Access Checker**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: iLUB Universität Bern

* **Test**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **Study Programme**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](http://www.ilias.de/docu/goto_docu_usr_45419.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)

* **Organisational Units**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
	* Tester: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)

* **Blog**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* 2nd Maintainer: MISSING
	* Testcases: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
	* Tester: MISSING

* **Login, Auth & Registration**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: MISSING
	* Tester: MISSING

* **Data Collection**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [kim.schmidt](http://www.ilias.de/docu/goto_docu_usr_28720.html)

* **Bibliographic List Item**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [marko.glaubitz](http://www.ilias.de/docu/goto_docu_usr_28309.html)

* **File**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Testcases: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)
	* Tester: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)


Components in the Service-Maintenance-Model:
* **UI-Service**
	* Coordinators: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html) 
	* Used in Directories: src/UI, 


The following directories are currently maintained unter the Classic-Maintenace-Model:
* Modules/Blog
 (1st Maintainer: MISSING)
* Services/AuthApache
 (1st Maintainer: MISSING)
* Services/Authentication
 (1st Maintainer: MISSING)
* Services/InfoScreen
 (1st Maintainer: MISSING)
* Services/Notes
 (1st Maintainer: MISSING)
* Services/Database
 (1st Maintainer: MISSING)
* Services/Excel
 (1st Maintainer: MISSING)
* Services/Init
 (1st Maintainer: MISSING)
* Services/PHPUnit
 (1st Maintainer: MISSING)
* Services/UIComponent
 (1st Maintainer: MISSING)
* Services/UICore
 (1st Maintainer: MISSING)
* Services/Utilities
 (1st Maintainer: MISSING)
* src/DI
 (1st Maintainer: MISSING)
* Modules/BookingManager
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html))
* Modules/MediaCast
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html))
* Services/Tagging
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_27631.html))
* Services/Style
 (1st Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html))
* Services/MathJax
 (1st Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html))
* Services/DiskQuota
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileDelivery
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileSystem
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileUpload
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/GlobalCache
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/VirusScanner
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/AuthShibboleth
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/ActiveRecord
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/BackgroundTask
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
* Services/Language
 (1st Maintainer: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html))
* Modules/StudyProgramme
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))
* Modules/Course
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))


The following directories are currently maintained unter the Service-Maintenace-Model:
* src/UI
 (Coordinator: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html))


The following directories are currently unmaintained:
* Modules/Category
* Modules/CategoryReference
* Modules/Chatroom
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
* Services/Dom
* Services/Environment
* Services/EventHandling
* Services/Exceptions
* Services/Export
* Services/Feeds
* Services/Form
* Services/Frameset
* Services/Help
* Services/History
* Services/Html
* Services/Http
* Services/Imprint
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
* Services/Survey
* Services/SystemCheck
* Services/Table
* Services/Taxonomy
* Services/TermsOfService
* Services/Tracking
* Services/Transformation
* Services/Tree
* Services/User
* Services/Verification
* Services/WebDAV
* Services/WebServices
* Services/WorkflowEngine
* Services/XHTMLPage
* Services/XHTMLValidator
* Services/Xml
* Services/YUI
* Services/jQuery
