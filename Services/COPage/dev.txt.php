<?php exit; ?>

=================================
handleCopiedContent
=================================

handleCopiedContent
- called by copyXmlContent
- called by pasteContents
-- called by ilPageEditorGUI->paste


=================================
handleCopiedContent
=================================

1. Page Rendering:

ilPCQuestion->getOnloadCode()
  - ilCOPageQuestionHandler.initCallback('".$url."');


2. Clicking Answer

Modules/Scorm2004/scripts/questions/question_handling.js
  - ilias.questions.checkAnswers
    - calls ilias.questions.<questiontype>(), e.g. ilias.questions.assMultipleChoice()
    - calls ->
ilCOPageQuestionHandler.js->processAnswer
  - ->sendAnswer sends asynRequers to ->
ilPageObjectGUI->processAnswer ->
ilPageQuestionProcessor->saveQuestionAnswer


=================================
Multi lang
=================================

- new table
- copg_multilang
  - defines default language per repository obj id (-> "-" records)
  -> page_object record with "-" in lang mean "de", but value is not
     set in page_object (no dependent tables need to be updated)
- copg_multilang_lang
  - contains all other languages supported by the repository object
- object_translation
  - general title/description multilinguality for objects
-> idea remove copg_multilang_lang (use object_translation instead) and
   rename copy_multilang to obj_copg_master_lang (only contain obj_id and master_lang)
- Support in Objects
  - Learning Modules
  - Categories
  - Magazin
  - Courses? Problem -> Rest of course (e.g. Course Information) is not multilingual
  - Groups? See course
  - Folders? Should only support, if courses and groups offer support
   
   ilObjectTranslationTableGUI
   
  
  
- page_object
  - new pk lang with "-" as default
- page_history
  - new pk lang with "-" as default
- ilPageObject
  - first reads copg_page_properties
  - always reads the default "-" record of page_object/page_history
  - ? if another language is set, a second ilPageObject is read into the master
  - change language (e.g. "-" -> "de") by parts of delete procedure and
    calling update() (-> usages and things are updated)
  - ilPageObject changes due to new pk "lang" in page_object (bwc backwards compatible for components not using multilinguality)
    - __construct  (bwc)
    - read  (bwc)
    - _exists (bwc)
    - _lookupActive (bwc)
    - _isScheduledActivation (bwc)
    - _writeActive (bwc)
    - _lookupActivationData (bwc)
    - createFromXML (bwc)
    - updateFromXML (bwc)
    - update (bwc)
    - _lookupContainsDeactivatedElements (bwc)
    - increaseViewCnt (bwc)
    - getRecentChanges (bwc)
    - getAllPages (bwc)
    - getNewPages (bwc)
    - getParentObjectContributors (! major change !, should be bwc)
    - getPageContributors (! major change !, should be bwc)
    - writeRenderedContent (bwc)
    - getPagesWithLinks (bwc)
  - ilPageUtil changes
    - _existsAndNotEmpty (bwc)
  - change due to new pk "lang" in page_history
    - read (bwc)
    - update (bwc)
    - getHistoryEntries (bwc)
    - getHistoryEntry (bwc)
    - getHistoryInfo (bwc)
  - open issues in page_object/ilPageObject
    - lookupParentId/_writeParentId: parent_id into copg_page_properties?
      - page_object.parent_id is accessed directly in Modules/Glossary/classes/class.ilGlossaryTerm.php
      - page_object.parent_id is accessed directly by Services/LinkChecker/classes/class.ilLinkChecker.php
    - what happens in current callUpdateListeners()?
    - import/export
    - search (page_object is accessed in Lucene.xml files; multilinguality?)
      - page_object accessed in Services/Search/classes/class.ilLMContentSearch.php
      - page_object accessed in Services/Search/classes/class.ilWikiContentSearch.php
    

Dependencies
  - int_link: new field source_lang, Services/Link/classes/class.ilInternalLink.php
  	-> make this a page service!
  	- db table extended (lang): done
  	- ilInternalLink adopted: done
  	- ilPageObject and ilPageObjectGUI adopted: done
  	- update and delete events refactored: done
  - page_style_usage: new field page_lang, Services/COPage/classes/class.ilPageObject.php
    -> page service 
    - db table extended (lang): done
    - update and delete events refactored: done
  - page_question (currently in saveInternalLinks)
    -> pc_question
    - db table extended (lang): done
    - update and delete events refactored / moved code to ilPCQuestion: done
  - mob_usage: new field usage_lang, Services/MediaObject/classes/class.ilObjMediaObject.php
    -> pc_mob
    - db table extended (lang): done
    - update and delete events refactored / moved code to ilPCMediaObject: done
  - page_anchor: new field page_lang, Services/COPage/classes/class.ilPageObject.php
    -> pc_paragraph
    - db table extended (lang): done
    - update and delete events refactored / moved code to ilPCParagraph: done
  - meta keywords (currently just added)
    -> pc_paragraph
    - update and delete events refactored / moved code to ilPCParagraph: done
  - file_usage: new field usage_lang, Modules/File/classes/class.ilObjFile.php
    -> pc_file_list, file links
    - db table extended (lang): done
    - update and delete events refactored / moved code to ilPCFileList: done
  - page_pc_usage: new field usage_lang, Services/COPage/classes/class.ilPageContentUsage.php
    -> pc_content_include, skill
    - db table extended (lang): done
    - update and delete events refactored / moved code to ilPCFileList: done
  
- update/updateXML must pass lang to dependencies done
- writeHistory must call dependencies done
- delete must call dependencies done
- modifyPageLanguage must call dependencies ??

- open
  - fix page copy/move procedures
  - fix intlinks on page/chapter copies
  - check if adopted ilInternalLink methods are used in other services than COPage

=================================
extends ilPageObject (18)
=================================

Modules/Blog (config intro)

Modules/DataCollection (config intro)

Modules/MediaPool (config intro, except ilmediapoolpageusagetable)

Modules/Scorm2004 (config intro)

Modules/Wiki (config intro)

Services/Imprint (config intro)

Services/Portfolio (config intro)

More:
Services/Style (config intro)

Modules/Glossary (config intro, except iltermusagetable)

Modules/Test (TestQuestionContent, Feedback)
Modules/TestQuestionPool

Modules/LearningModule (Unirep Branch)

Services/Container (config intro)
Modules/Category
Modules/Course
Modules/Folder
Modules/Group
Modules/ItemGroup
Modules/RootFolder

Services/Authentication (config intro)
Services/Init

Services/Help

Services/MediaObjects

Services/Payment (config intro)




=================================
extends ilPageContent ocurrences (24)
=================================

/htdocs/ilias2/Services/COPage/classes/class.ilPCBlog.php
36: class ilPCBlog extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCContentInclude.php
17: class ilPCContentInclude extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCFileItem.php
class ilPCFileItem extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCFileList.php
36: class ilPCFileList extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCilPCInteractiveImage.php
15: class ilPCInteractiveImage extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCList.php
16: class ilPCList extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCListItem.php
36: class ilPCListItem extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCLoginPageElements.php
16: class ilPCLoginPageElements extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCMap.php
class ilPCMap extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCMediaObject.php
16: class ilPCMediaObject extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCParagraph.php
17: class ilPCParagraph extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCPlaceHolder.php
37: class ilPCPlaceHolder extends ilPageContent {
/htdocs/ilias2/Services/COPage/classes/class.ilPCPlugged.php
35: class ilPCPlugged extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCProfile.php
36: class ilPCProfile extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCQuestion.php
36: class ilPCQuestion extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCQuestionOverview.php
15: class ilPCQuestionOverview extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCResources.php
17: class ilPCResources extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCSection.php
17: class ilPCSection extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCSkills.php
36: class ilPCSkills extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCTab.php
36: class ilPCTab extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCTable.php
17: class ilPCTable extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCTableData.php
36: class ilPCTableData extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCTabs.php
36: class ilPCTabs extends ilPageContent
/htdocs/ilias2/Services/COPage/classes/class.ilPCVerification.php
36: class ilPCVerification extends ilPageContent




