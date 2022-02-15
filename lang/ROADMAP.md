# Language Roadmap (v0.3)

All users get in touch with language during their usage of ILIAS. So do all people helping to write concepts and features, those writing the test cases and – of course – the maintainers and coders who provide the actual implementation.

The variety of actors leads to different flavours of language in ILIAS. The creation of a general style book has not yet gained enough traction.

Still, we would like to improve language in ILIAS. This is why we want to… 
* outline what we’re up to and 
* provide pull requests based on our ideas presented in this document.

If you oppose (or support!) entries in this document, please comment and/or get on board.

## Unifying language variables

Language variables are one of the many cross-cutting issues in ILIAS. Over time, the language increasingly diffuses apart. Continuous maintenance of the language variables should ensure and *improve the uniformity of the system*. This chapter describes the procedure and outlines the steps to unify the language variables. The priorities are discussed frequently with the language maintainer.

### Tab Navigation: Title of "Settings" tab and "General" Settings sub tab

The "Settings" tab is used in many ILIAS objects and services. The structure is very similar in all contexts of use – but the wording often differs. In order to simplify the handling and the learnability of the settings, wording should be adapted to each other.

**Suggested Pattern**
- The main tab for the settings of an object should always be called "Settings".
- There should be only one Settings tab per object or service.
- Since you are already in the Settings tab, the sub tabs should not get the addition "... Settings". Existing redundancies should be improved.
- If there is a sub tab that entails a variety of basic and general settings, it should be called "General". Other sub tabs should be named according to the settings groups contained.

**Currently used labels for the Basic/General Settings SUB tab**
"Settings"
  - ILIAS Learning Module
  - Category
  - Study Programme
  - Glossary
  - Survey
  - Mediapool
  - Content Page
  - xAPI/cmi5
  - Bibliography
  - Administration > Personal Workspace > Who is online?-Tool
  - Administration > Personal Workspace > Tagging (Main tab here is called "Edit Settings", which should be corrected)

"General Settings"
  - Test&Assessment
  - Question Pool
  - Wiki
  - Exercise
  - Administration > Personal Workspace > Dashboard
  - Administration > Repository and Objects > Course
  - Administration > Repository and Objects > Group

"General"
  - Administration > Layout and Navigation > Editing
  - Administration > Communication > Mail
  - Administration > Search and Find > Metadata (Reiter heißt General Settings)

"Basic Settings"
  - Forum
  - Administration > System Settings and Maintenance > General Settings

"[Object name] Settings"
  - Course
  - Group
  - Administration > System Settings and Maintenance > Logging
  - Administration > User and Roles > Authentication and Registration (?eventuell Sonderfall? - mehrere Tabs mit Subeinstellungen)

"[Object] Properties"
  - Blog
  - Portfolio Template

"Settings for [Object name]"
  - SCORM Learning Module 1.2

"Edit"
  - Individual Assessment

"Object Settings [sic!]"
  - LTI Consumer

"Edit Settings"
  - Administration > Communication > News and Web Feeds (Reiter heißt Edit Settings) --> BUG REPORT

No Settings Tab
  - Administration > System Settings and Maintenance > System Check
  - Administration > System Settings and Maintenance > Repository Trash and Permissions (falschen Titel) --> BUG REPORT
  - Administration > Layout and Navigation > Main Menu
  - Administration > Layout and Navigation > Layout and Styles
  - Administration > User and Roles > terms of Service

Setting duplicates
  - Administration > Repository and Objects > Survey (Settings und Settings Templates)
  - Administration > Repository and Objects > Test (Settings und Settings Templates)

### Naming of editors in ILIAS
#### ILIAS Page Editor, TinyMCE Editor, Content Style Editor

There are many different names for editors. In some places only the spelling differs, in others different terms are used for the same editor. 

**Suggested Pattern**
- The ILIAS Page Editor should always be called "ILIAS Page Editor" (en) / "ILIAS-Seiteneditor" (de).
- The TinyMCE Editor should always be called "TinyMCE Editor" (en) / "TinyMCE-Editor" (de).
- The Content Style Editor should always be called "Content Style Editor" (en) / "Content-Style-Editor" (de).
- If an editor is used in a specific context, the editor term should not be adjusted, e.g. "Editor for Answers" (NO) / "To edit answers, use the TinyMCE editor" (YES). 

**Currently used labels for ILIAS-Page Editor/TinyMCE Editor**
"ILIAS-Page Editor_en"
    - ILIAS Page Editor (en)
    - Loginscreen Editor (en)

"ILIAS-Page Editor_de"
    - Seiteneditor (de)
    - ILIAS-Seiteneditor (de)
    - Editor (de)
    - Standard-Seiteneditor (de)
    - ILIAS-Editor -> ILIAS-Editor (de)

"TinyMCE-Editor_en"
    - Rich-text editor (en)
    - Rich-Text-Editor (TinyMCE) (en)

"TinyMCE-Editor_de"
    - TinyMCE (de)
    - TinyMCE-Editor (de)
    - Rich-Text-Editor (TinyMCE) (de)
    - Rich-Text-Editor (de)
    - Tiny MCE Editor (de)

"Content Style-Editor"
		- Styleeditor (de)

#### Other editor labels

These should be looked at, too!
    - certificate editor (en) / Zertifikat-Editor (de)
		- editor for answers (en) / Antwort-Editor (de)
		- portfolio editor (en) / Portfolio-Editor (de)

### Customize Page

**Suggested Pattern**
- The action to open the ILIAS Page Editor should always be Customize Page.
- The action to close the ILIAS Page Editor should always be ?????. (Oliver fragen)

**Currently used labels for Customize Page / Finish Editing**
		- Customize Page (en) / Seite gestalten (de)
		- Edit (en) / Bearbeiten (de)
		- Loginscreen Editor (en) / Loginseite gestalten (de)
    - Finish Editing (en) / Bearbeitung beenden (de)

### Insert links via ILIAS Page Editor

**Currently used labels for Customize Page / Finish Editing**
Text
    - Internal Link (en) / Interner Link (de)
    - External Link (en) / Externer Link (de)
    - User Profil (en) / Benutzerprofil (de)

Section
    - No Link (en) / Kein Link (de)
    - World Wide Web (en) / WWW / Internet (de)
        - » Select Target Object (en) / Objekt » Ziel-Objekt wählen (de)
    - Inside ILIAS (en) / Innerhalb von ILIAS (de)

Media
    - Link (external) (en) / Link (extern) (de)
    - Link (internal) (en) / Link (intern) (de)
        - [get link] (en) / [Link auswählen] (de)
    - No Link (en) / Kein Link (de)

### Further simple adjustments

The following issues were collected by Alix Mela. We would like to discuss and improve them as we proceed with our work. Further additions are welcome!

**Needs to be discussed**
- E-Mail-Adresse vs. E-Mailadresse
- Mail vs. E-Mail vs. ?
- Popup achnge to Pop-up (Duden)
- Plugin change to Plug-in (Duden)
- Addon vs. Add-on
- Account vs. Konto vs. Name (Benutzer-Account, Benutzerkonto, Benutzername, Benutzerkennung, Benutzerkontennamen)
- https vs. HTTPS http vs. HTTP
- Less vs. LESS vs. less
- strong vs. Bold
- Brotkrumennavigation, pfad, Brotkrumendarstellung
- typ vs typen
- Content vs. Inhalt vs. ?
- mastery score vs. mastery-score
- 4ten vs. 4.
- File type capitalized YES/NO (svg vs. SVG) and what about compound words?
- How are things mentioned? (Quotation marks, b, strong, i, ...?)
- Slash with/without space
- Write out umlauts or à la &uuml;
- Login vs. Anmeldung
- Dopdown vs. Drop-down vs. Drop-Down vs. Drop-down-Menü (Duden)
- URL m/w
- RefId, UserId, ObjId
- Upload vs. Hochladen
- Download vs. Herunterladen
- (n) vs. (-n) vs. /-n
- … vs. Space

### Introduction of further labels
We also want to discuss and find German names for the following labels.

**Currently used labels**
- Background Task
- Notification Center
- Learning Record Store
- Activity (LTI)





