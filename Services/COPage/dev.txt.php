<?php exit; ?>


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
    


- open
  - fix page copy/move procedures
  - fix intlinks on page/chapter copies
  - check if adopted ilInternalLink methods are used in other services than COPage