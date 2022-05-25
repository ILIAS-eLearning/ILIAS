<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";
/** @defgroup ModulesScormAicc Modules/ScormAicc
 */
/**
* Class ilObjSCORMLearningModule
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSAHSLearningModule extends ilObject
{
    private string $api_func_prefix;
    private string $credit_mode;
    private string $lesson_mode;
    private int $style_id;
    private string $auto_review;
    private int $max_attempt;
    private int $module_version;
    private int $assigned_glossary;
    private bool $session;
    private bool $no_menu;
    private bool $hide_navig;
    private bool $ie_force_render;
    private bool $fourth_edition;
    private bool $interactions;
    private bool $objectives;
    private bool $comments;
    private bool $time_from_lms;
    private bool $check_values;
    private bool $debug;
    private bool $auto_continue;
    private bool $auto_last_visited;
    private bool $auto_suspend;
    private int $open_mode;
    private int $width;
    private int $height;
    private ?int $mastery_score;
    private int $id_setting;
    private int $name_setting;
    private string $sub_type;
    protected bool $sequencing = false;

    protected string $localization = "";

    protected string $mastery_score_values = "";

    protected int $tries;

    protected string $api_adapter;

    /**
    * Constructor
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "sahs";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * create file based lm
     */
    public function create(bool $upload = false) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $id = parent::create();
        if (!$upload) {
            $this->createMetaData();
        }

        $this->createDataDirectory();
        $ilDB->manipulateF(
            '
			INSERT INTO sahs_lm (id, api_adapter, c_type, editable, seq_exp_mode,localization) 
			VALUES (%s,%s,%s,%s,%s,%s)',
            array('integer', 'text', 'text', 'integer','integer','text'),
            array($this->getId(),'API', $this->getSubType(),(int) $this->getEditable(),
                0, $this->getLocalization()
                )
        );
        return $id;
    }

    /**
     * read object
     *
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     */
    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::read();

        $lm_set = $ilDB->queryF(
            'SELECT * FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($this->getId())
        );
        
        while ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
            $this->setAutoReviewChar($lm_rec["auto_review"]);
            $this->setAPIAdapterName($lm_rec["api_adapter"]);
            $this->setDefaultLessonMode($lm_rec["default_lesson_mode"]);
            $this->setAPIFunctionsPrefix($lm_rec["api_func_prefix"]);
            $this->setCreditMode($lm_rec["credit"]);
            $this->setSubType($lm_rec["c_type"]);
//            $this->setEditable(false);
            $this->setStyleSheetId((int) $lm_rec["stylesheet"]);
            $this->setMaxAttempt((int) $lm_rec["max_attempt"]);
            $this->setModuleVersion((int) $lm_rec["module_version"]);
            $this->setAssignedGlossary((int) $lm_rec["glossary"]);
            $this->setTries((int) $lm_rec["question_tries"]);
            $this->setLocalization((string) $lm_rec["localization"]);
            $this->setSession(ilUtil::yn2tf($lm_rec["unlimited_session"]));
            $this->setNoMenu(ilUtil::yn2tf($lm_rec["no_menu"]));
            $this->setHideNavig(ilUtil::yn2tf($lm_rec["hide_navig"]));
            $this->setFourth_edition(ilUtil::yn2tf($lm_rec["fourth_edition"]));
            $this->setSequencing(ilUtil::yn2tf($lm_rec["sequencing"]));
            $this->setInteractions(ilUtil::yn2tf($lm_rec["interactions"]));
            $this->setObjectives(ilUtil::yn2tf($lm_rec["objectives"]));
            $this->setComments(ilUtil::yn2tf($lm_rec["comments"]));
            $this->setTime_from_lms(ilUtil::yn2tf($lm_rec["time_from_lms"]));
            $this->setDebug(ilUtil::yn2tf($lm_rec["debug"]));
//            $this->setDebugPw($lm_rec["debugpw"]);
//            $this->setSequencingExpertMode(bool $lm_rec["seq_exp_mode"]);
            $this->setOpenMode((int) $lm_rec["open_mode"]);
            $this->setWidth((int) $lm_rec["width"]);
            $this->setHeight((int) $lm_rec["height"]);
            $this->setAutoContinue(ilUtil::yn2tf($lm_rec["auto_continue"]));
            $this->setAuto_last_visited(ilUtil::yn2tf($lm_rec["auto_last_visited"]));
            $this->setCheck_values(ilUtil::yn2tf($lm_rec["check_values"]));
//            $this->setOfflineMode(ilUtil::yn2tf($lm_rec["offline_mode"]));
            $this->setAutoSuspend(ilUtil::yn2tf($lm_rec["auto_suspend"]));
            $this->setIe_force_render(ilUtil::yn2tf($lm_rec["ie_force_render"]));
            $this->setMasteryScore($lm_rec["mastery_score"]);
            $this->setIdSetting((int) $lm_rec["id_setting"]);
            $this->setNameSetting((int) $lm_rec["name_setting"]);
            if (ilObject::_lookupType($this->getStyleSheetId()) !== "sty") {
                $this->setStyleSheetId(0);
            }
        }
    }

    /**
     * Get affective localization
     */
    public static function getAffectiveLocalization(int $a_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $lm_set = $ilDB->queryF(
            'SELECT localization FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_id)
        );
        $lm_rec = $ilDB->fetchAssoc($lm_set);
        $inst_lang = $lng->getInstalledLanguages();
        if ($lm_rec["localization"] != "" && in_array($lm_rec["localization"], $inst_lang)) {
            return $lm_rec["localization"];
        }
        return $lng->getLangKey();
    }

    /**
     * lookup subtype id (scorm, )
     */
    public static function _lookupSubType(int $a_obj_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $obj_set = $ilDB->queryF(
            'SELECT c_type FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_obj_id)
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        
        return $obj_rec["c_type"];
    }

//    /**
//    * Set Editable.
//    *
//    * @param	boolean	$a_editable	Editable
//    */
//    public function setEditable(bool $a_editable) : void
//    {
//        $this->editable = $a_editable;
//    }

    public function getEditable() : bool
    {
        return false;
    }

    /**
     * Set default tries for questions
     */
    public function setTries(int $a_tries) : void
    {
        $this->tries = $a_tries;
    }

    public function getTries() : int
    {
        return $this->tries;
    }

    public function setLocalization(string $a_val) : void
    {
        $this->localization = $a_val;
    }

    public function getLocalization() : string
    {
        return $this->localization;
    }

    /**
     * obsolet?
     */
    public static function _getTries(int $a_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $lm_set = $ilDB->queryF(
            'SELECT question_tries FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($a_id)
        );
        $lm_rec = $ilDB->fetchAssoc($lm_set);
            
        return (int) $lm_rec['question_tries'];
    }

    /**
    * Gets the disk usage of the object in bytes.
    *
    * @return integer the disk usage in bytes
    */
    public function getDiskUsage() : int
    {
        return ilObjSAHSLearningModuleAccess::_lookupDiskUsage($this->id);
    }


    /**
    * creates data directory for package files
    * ("./data/lm_data/lm_<id>")
    */
    public function createDataDirectory() : void
    {
        $lm_data_dir = ilFileUtils::getWebspaceDir() . "/lm_data";
        ilFileUtils::makeDir($lm_data_dir);
        ilFileUtils::makeDir($this->getDataDirectory());
    }

    /**
     * get data directory of lm
     */
    public function getDataDirectory(?string $mode = "filesystem") : string
    {
        $lm_data_dir = ilFileUtils::getWebspaceDir($mode) . "/lm_data";
        return $lm_data_dir . "/lm_" . $this->getId();
    }

    /**
     * get api adapter name
     */
    public function getAPIAdapterName() : string
    {
        return $this->api_adapter;
    }

    /**
     * set api adapter name
     */
    public function setAPIAdapterName(string $a_api) : void
    {
        $this->api_adapter = $a_api;
    }

    /**
     * get api functions prefix
     * @return string
     */
    public function getAPIFunctionsPrefix() : string
    {
        return $this->api_func_prefix;
    }

    /**
     * set api functions prefix
     */
    public function setAPIFunctionsPrefix(string $a_prefix) : void
    {
        $this->api_func_prefix = $a_prefix;
    }

    /**
     * get credit mode
     */
    public function getCreditMode() : string
    {
        return $this->credit_mode;
    }

    /**
     * set credit mode
     */
    public function setCreditMode(string $a_credit_mode) : void
    {
        $this->credit_mode = $a_credit_mode;
    }

    /**
     * set default lesson mode
     */
    public function setDefaultLessonMode(string $a_lesson_mode) : void
    {
        $this->lesson_mode = $a_lesson_mode;
    }

    /**
     * get default lesson mode
     */
    public function getDefaultLessonMode() : string
    {
        global $DIC;
        if ($DIC->user()->getId() == 13) {
            return "browse";
        }
        return $this->lesson_mode;
    }

    /**
     * get ID of assigned style sheet object
     */
    public function getStyleSheetId() : int
    {
        return $this->style_id;
    }

    /**
     * set ID of assigned style sheet object
     */
    public function setStyleSheetId(int $a_style_id) : void
    {
        $this->style_id = $a_style_id;
    }

    /**
     * set auto review as true/false for SCORM 1.2
     */
    public function setAutoReview(bool $a_auto_review) : void
    {
        $this->auto_review = ilUtil::tf2yn($a_auto_review);
    }

    /**
     * get auto review as true/false for SCORM 1.2
     */
    public function getAutoReview() : bool
    {
        return ilUtil::yn2tf($this->auto_review);
    }

    /**
     * set auto review as Char for SCORM 2004
     */
    public function setAutoReviewChar(string $a_auto_review) : void
    {
        $this->auto_review = $a_auto_review;
    }

    /**
     * get auto review as Char for SCORM 2004
     */
    public function getAutoReviewChar() : string
    {
        return $this->auto_review;
    }

    public function getMaxAttempt() : int
    {
        return $this->max_attempt;
    }

    public function setMaxAttempt(int $a_max_attempt) : void
    {
        $this->max_attempt = $a_max_attempt;
    }

    public function getModuleVersion() : int
    {
        return $this->module_version;
    }

    public function getAssignedGlossary() : int
    {
        return $this->assigned_glossary;
    }

    public function setAssignedGlossary(int $a_assigned_glossary) : void
    {
        $this->assigned_glossary = $a_assigned_glossary;
    }

    public function setModuleVersion(int $a_module_version) : void
    {
        $this->module_version = $a_module_version;
    }

    public function getSession() : bool
    {
        return $this->session;
    }

    public function setSession(bool $a_session) : void
    {
        $this->session = $a_session;
    }

    /**
     * disable menu
     */
    public function getNoMenu() : bool
    {
        return $this->no_menu;
    }

    /**
     * disable menu
     */
    public function setNoMenu(bool $a_no_menu) : void
    {
        $this->no_menu = $a_no_menu;
    }

    /**
     * hide navigation tree
     */
    public function getHideNavig() : bool
    {
        return $this->hide_navig;
    }

    /**
     * disable menu
     */
    public function setHideNavig(bool $a_hide_navig) : void
    {
        $this->hide_navig = $a_hide_navig;
    }

    /**
     * BrowserCacheDisabled for SCORM 2004 / ENABLE_JS_DEBUG
     */
    public function getCacheDeactivated() : bool
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $lm_set = new ilSetting("lm");
        return $lm_set->get("scormdebug_disable_cache") == "1";
    }

    /**
     * sessionDisabled for SCORM 2004
     */
    public function getSessionDeactivated() : bool
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $lm_set = new ilSetting("lm");
        return $lm_set->get("scorm_without_session") == "1";
    }

    /**
     * debugActivated
     */
    public function getDebugActivated() : bool
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $lm_set = new ilSetting("lm");
        return $lm_set->get("scormdebug_global_activate") === "1";
    }

    /**
     * force Internet Explorer to render again after some Milliseconds - useful for learning Modules with a lot of iframes or frames and IE >=10
     */
    public function getIe_force_render() : bool
    {
        return $this->ie_force_render;
    }

    public function setIe_force_render(bool $a_ie_force_render) : void
    {
        $this->ie_force_render = $a_ie_force_render;
    }

    /**
     * SCORM 2004 4th edition features
     */
    public function getFourth_Edition() : bool
    {
        return $this->fourth_edition;
    }

    public function setFourth_edition(bool $a_fourth_edition) : void
    {
        $this->fourth_edition = $a_fourth_edition;
    }

    public function getSequencing() : bool
    {
        return $this->sequencing;
    }

    public function setSequencing(bool $a_sequencing) : void
    {
        $this->sequencing = $a_sequencing;
    }

    public function getInteractions() : bool
    {
        return $this->interactions;
    }

    public function setInteractions(bool $a_interactions) : void
    {
        $this->interactions = $a_interactions;
    }

    public function getObjectives() : bool
    {
        return $this->objectives;
    }

    public function setObjectives(bool $a_objectives) : void
    {
        $this->objectives = $a_objectives;
    }

    public function getComments() : bool
    {
        return $this->comments;
    }

    public function setComments(bool $a_comments) : void
    {
        $this->comments = $a_comments;
    }

    public function getTime_from_lms() : bool
    {
        return $this->time_from_lms;
    }

    public function setTime_from_lms(bool $a_time_from_lms) : void
    {
        $this->time_from_lms = $a_time_from_lms;
    }

    public function getCheck_values() : bool
    {
        return $this->check_values;
    }

    public function setCheck_values(bool $a_check_values) : void
    {
        $this->check_values = $a_check_values;
    }

//    /**
//    * offlineMode
//    */
//    public function getOfflineMode()
//    {
//        return $this->offline_mode;
//    }
//
//    public function setOfflineMode($a_offline_mode)
//    {
//        $this->offline_mode = $a_offline_mode;
//    }

    public function getDebug() : bool
    {
        return $this->debug;
    }

    public function setDebug(bool $a_debug) : void
    {
        $this->debug = $a_debug;
    }
    
//    /**
//    * debug pw
//    */
//    public function getDebugPw()
//    {
//        return $this->debug_pw;
//    }
//
//    /**
//    * debug pw
//    */
//    public function setDebugPw($a_debug_pw)
//    {
//        $this->debug_pw = $a_debug_pw;
//    }

    public function setAutoContinue(bool $a_auto_continue) : void
    {
        $this->auto_continue = $a_auto_continue;
    }

    public function getAutoContinue() : bool
    {
        return $this->auto_continue;
    }

    public function getAuto_last_visited() : bool
    {
        return $this->auto_last_visited;
    }

    public function setAuto_last_visited(bool $a_auto_last_visited) : void
    {
        $this->auto_last_visited = $a_auto_last_visited;
    }

    
//    /**
//     * Set sequencing expert mode
//     *
//     * @param boolean $a_val sequencing expert mode
//     */
//    public function setSequencingExpertMode(bool $a_val)
//    {
//        $this->seq_exp_mode = $a_val;
//    }
//
//    /**
//     * Get sequencing expert mode
//     *
//     * @return boolean sequencing expert mode
//     */
//    public function getSequencingExpertMode()
//    {
//        return $this->seq_exp_mode;
//    }

    public function setAutoSuspend(bool $a_auto_suspend) : void
    {
        $this->auto_suspend = $a_auto_suspend;
    }

    public function getAutoSuspend() : bool
    {
        return $this->auto_suspend;
    }

    /**
     * open_mode
     * 0: in Tab/new Window like in previous versions
     * 1: in iFrame with width=100% and heigth=100%
     * 2: in iFrame with specified width and height
     * 3:
     * 4:
     * 5: in new Window without specified width and height
     * 6: in new Window with specified width and height
     */
    public function getOpenMode() : int
    {
        return $this->open_mode;
    }

    public function setOpenMode(int $a_open_mode) : void
    {
        $this->open_mode = $a_open_mode;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function setWidth(int $a_width) : void
    {
        $this->width = $a_width;
    }

    public function getHeight() : int
    {
        return $this->height;
    }

    public function setHeight(int $a_height) : void
    {
        $this->height = $a_height;
    }

    public function getMasteryScore() : ?int
    {
        return $this->mastery_score;
    }

    public function setMasteryScore(?int $a_mastery_score) : void
    {
        $this->mastery_score = $a_mastery_score;
    }
    
    /**
    * check mastery_score / min_normalized_measure of SCOs (SCORM 1.2) / objectives (SCORM 2004)
    */
    public function checkMasteryScoreValues() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $s_result = "";
        $a_result = array();
        $type = self::_lookupSubType($this->getID());

        if ($type === "scorm2004") {
            $set = $ilDB->query("SELECT minnormalmeasure FROM cp_objective, cp_node" .
                " WHERE satisfiedbymeasure=1 AND minnormalmeasure is not null AND cp_objective.cp_node_id=cp_node.cp_node_id AND" .
                " slm_id = " . $ilDB->quote($this->getID(), "integer"));
            while ($rec = $ilDB->fetchAssoc($set)) {
                $tmpval = $rec["minnormalmeasure"] * 100;
                if (!in_array($tmpval, $a_result)) {
                    $a_result[] = $tmpval;
                }
            }
        } else {
            $set = $ilDB->query("SELECT masteryscore FROM sc_item,scorm_object" .
                " WHERE sc_item.masteryscore is not null AND sc_item.obj_id=scorm_object.obj_id AND" .
                " slm_id = " . $ilDB->quote($this->getID(), "integer"));
            while ($rec = $ilDB->fetchAssoc($set)) {
                if (!in_array($rec["masteryscore"], $a_result)) {
                    $a_result[] = $rec["masteryscore"];
                }
            }
        }
        $s_result = implode(", ", $a_result);
        $this->mastery_score_values = $s_result;
    }

    public function getMasteryScoreValues() : string
    {
        return $this->mastery_score_values;
    }

    public function getIdSetting() : int
    {
        return $this->id_setting;
    }

    public function setIdSetting(int $a_id_setting) : void
    {
        $this->id_setting = $a_id_setting;
    }

    public function getNameSetting() : int
    {
        return $this->name_setting;
    }

    public function setNameSetting(int $a_name_setting) : void
    {
        $this->name_setting = $a_name_setting;
    }

    public function update() : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $this->updateMetaData();
        parent::update();
        
        $s_mastery_score = $this->getMasteryScore();
        if ($s_mastery_score == "") {
            $s_mastery_score = null;
        }

        $statement = $ilDB->manipulateF(
            '
			UPDATE sahs_lm  
			SET api_adapter = %s, 
				api_func_prefix = %s,
				auto_review = %s,
				default_lesson_mode = %s,
				c_type = %s,
				stylesheet = %s, 
				editable = %s, 
				max_attempt = %s, 
				module_version = %s, 
				credit = %s, 
				glossary = %s, 
				question_tries = %s,
				unlimited_session = %s,
				no_menu = %s,
				hide_navig = %s,
				fourth_edition =%s,
				sequencing = %s,
				interactions = %s,
				objectives = %s,
				comments = %s,
				time_from_lms = %s,
				debug = %s,
				localization = %s,
				seq_exp_mode = %s,
				open_mode = %s,
				width = %s,
				height = %s,
				auto_continue = %s,
				auto_last_visited = %s,
				check_values = %s,
				auto_suspend = %s,
				ie_force_render = %s,
				mastery_score = %s,
				id_setting = %s,
				name_setting = %s
			WHERE id = %s',
            array(	'text',
                'text',
                'text',
                'text',
                'text',
                'integer',
                'integer',
                'integer',
                'integer',
                'text',
                'integer',
                'integer',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'text',
                'integer',
                'integer',
                'integer',
                'integer',
                'text',
                'text',
                'text',
                'text',
                'text',
                'integer',
                'integer',
                'integer',
                'integer'
                ),
            array(	$this->getAPIAdapterName(),
                $this->getAPIFunctionsPrefix(),
                $this->getAutoReviewChar(),
                $this->getDefaultLessonMode(),
                $this->getSubType(),
                $this->getStyleSheetId(),
                $this->getEditable(),
                $this->getMaxAttempt(),
                $this->getModuleVersion(),
                $this->getCreditMode(),
                $this->getAssignedGlossary(),
                $this->getTries(),
                ilUtil::tf2yn($this->getSession()),
                ilUtil::tf2yn($this->getNoMenu()),
                ilUtil::tf2yn($this->getHideNavig()),
                ilUtil::tf2yn($this->getFourth_edition()),
                ilUtil::tf2yn($this->getSequencing()),
                ilUtil::tf2yn($this->getInteractions()),
                ilUtil::tf2yn($this->getObjectives()),
                ilUtil::tf2yn($this->getComments()),
                ilUtil::tf2yn($this->getTime_from_lms()),
                ilUtil::tf2yn($this->getDebug()),
                $this->getLocalization(),
                0,//$this->getSequencingExpertMode(),
                $this->getOpenMode(),
                $this->getWidth(),
                $this->getHeight(),
                ilUtil::tf2yn($this->getAutoContinue()),
                ilUtil::tf2yn($this->getAuto_last_visited()),
                ilUtil::tf2yn($this->getCheck_values()),
                ilUtil::tf2yn($this->getAutoSuspend()),
                ilUtil::tf2yn($this->getIe_force_render()),
                $s_mastery_score,
                $this->getIdSetting(),
                $this->getNameSetting(),
                $this->getId())
        );

        return true;
    }

    /**
     * Get SCORM modules that assign a certain glossary
     */
    public static function getScormModulesForGlossary(int $a_glo_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
    
        $set = $ilDB->query("SELECT DISTINCT id FROM sahs_lm WHERE " .
            " glossary = " . $ilDB->quote($a_glo_id, "integer"));
        $sms = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (ilObject::_hasUntrashedReference($rec["id"])) {
                $sms[] = $rec["id"];
            }
        }
        return $sms;
    }

    /**
     * Get SCORM modules that assign a certain glossary
     */
    public static function lookupAssignedGlossary(int $a_slm_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();
    
        $set = $ilDB->query("SELECT DISTINCT glossary FROM sahs_lm WHERE " .
            " id = " . $ilDB->quote($a_slm_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        $glo_id = $rec["glossary"];
        if (ilObject::_lookupType($glo_id) === "glo") {
            return $glo_id;
        }
        return 0;
    }

    public function setSubType(string $a_sub_type) : void
    {
        $this->sub_type = $a_sub_type;
    }

    public function getSubType() : string
    {
        return $this->sub_type;
    }

    /**
    * delete SCORM learning module and all related data
    *
    * this method has been tested on may 9th 2004
    * meta data, scorm lm data, scorm tree, scorm objects (organization(s),
    * manifest, resources and items), tracking data and data directory
    * have been deleted correctly as desired
    *
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete() : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilLog = ilLoggerFactory::getLogger('sahs');

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
 
        // delete meta data of scorm content object
        $this->deleteMetaData();

        // delete data directory
        ilFileUtils::delDir($this->getDataDirectory());

        // delete scorm learning module record
        $ilDB->manipulateF(
            'DELETE FROM sahs_lm WHERE id = %s',
            array('integer'),
            array($this->getId())
        );
        
        $ilLog->write("SAHS Delete(SAHSLM), Subtype: " . $this->getSubType());
        
        if ($this->getSubType() === "scorm") {
            $sc_tree = new ilSCORMTree($this->getId());
            $r_id = $sc_tree->readRootId();
            if ($r_id > 0) {
                $items = $sc_tree->getSubTree($sc_tree->getNodeData($r_id));
                foreach ($items as $item) {
                    $sc_object = ilSCORMObject::_getInstance($item["obj_id"], $this->getId());
                    if (is_object($sc_object)) {
                        $sc_object->delete();
                    }
                }
                $sc_tree->removeTree($sc_tree->getTreeId());
            }
        }

        if ($this->getSubType() !== "scorm") {
            // delete aicc data
            $res = $ilDB->queryF(
                '
				SELECT aicc_object.obj_id FROM aicc_object, aicc_units
				WHERE aicc_object.obj_id = aicc_units.obj_id
				AND aicc_object.slm_id = %s',
                array('integer'),
                array($this->getId())
            );
            
            while ($row = $ilDB->fetchAssoc($res)) {
                $obj_id = $row['obj_id'];
                $ilDB->manipulateF(
                    '
					DELETE FROM aicc_units WHERE obj_id = %s',
                    array('integer'),
                    array($obj_id)
                );
            }
            
            $res = $ilDB->queryF(
                '
				SELECT aicc_object.obj_id FROM aicc_object, aicc_course
				WHERE aicc_object.obj_id = aicc_course.obj_id
				AND aicc_object.slm_id = %s',
                array('integer'),
                array($this->getId())
            );
            
            while ($row = $ilDB->fetchAssoc($res)) {
                $obj_id = $row['obj_id'];
                $ilDB->manipulateF(
                    '
					DELETE FROM aicc_course WHERE obj_id = %s',
                    array('integer'),
                    array($obj_id)
                );
            }

            $ilDB->manipulateF(
                '
				DELETE FROM aicc_object WHERE slm_id = %s',
                array('integer'),
                array($this->getId())
            );
        }

        $q_log = "DELETE FROM scorm_tracking WHERE obj_id = " . $ilDB->quote($this->getId());
        $ilLog->write("SAHS Delete(SAHSLM): " . $q_log);

        $ilDB->manipulateF(
            'DELETE FROM scorm_tracking WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );

        $q_log = "DELETE FROM sahs_user WHERE obj_id = " . $ilDB->quote($this->getId());
        $ilLog->write("SAHS Delete(SAHSLM): " . $q_log);

        $ilDB->manipulateF(
            'DELETE FROM sahs_user WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );

        // always call parent delete function at the end!!
        return true;
    }

    /**
     * Returns the points in percent for the learning module
     * This is called by the certificate generator if [SCORM_POINTS] is
     * inserted.
     */
    public function getPointsInPercent() : ?float
    {
        global $DIC;
        $ilUser = $DIC->user();
        if (strcmp($this->getSubType(), "scorm2004") == 0) {
            $res = ilObjSCORM2004LearningModule::_getUniqueScaledScoreForUser($this->getId(), $ilUser->getId());
            if (!is_null($res)) {
                return $res * 100.0;
            }

            return $res;
        }

        return null;
    }

    /**
     * Returns score.max for the learning module, refered to the last sco where score.max is set.
     * This is called by the certificate generator if [SCORM_POINTS_MAX] is
     * inserted.
     */
    public function getMaxPoints() : ?float
    {
        global $DIC;
        $ilUser = $DIC->user();
        
        if (strcmp($this->getSubType(), 'scorm2004') == 0) {
            $res = ilObjSCORM2004LearningModule::_getMaxScoreForUser($this->getId(), $ilUser->getId());
            return $res;
        }

        return null;
    }

    /**
     * Populate by directory. Add a filename to do a special check for
     * ILIAS SCORM export files. If the corresponding directory is found
     * within the passed directory path (i.e. "htlm_<id>") this
     * subdirectory is used instead.
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    public function populateByDirectoy(string $a_dir, string $a_filename = "") : void
    {
        /*preg_match("/.*sahs_([0-9]*)\.zip/", $a_filename, $match);
        if (is_dir($a_dir."/sahs_".$match[1]))
        {
            $a_dir = $a_dir."/sahs_".$match[1];
        }*/
        ilFileUtils::rCopy($a_dir, $this->getDataDirectory());
        ilFileUtils::renameExecutables($this->getDataDirectory());
    }

    /**
     * Clone scorm object
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilSaxParserException
     */
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObject
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        if ($new_obj !== null) {
            $this->cloneMetaData($new_obj);

            $new_obj->setOfflineStatus($this->getOfflineStatus());
            //copy online status if object is not the root copy object
            $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);
            if ($cp_options->isRootNode($this->getRefId())) {
                $new_obj->setOfflineStatus(true);
            }

            // copy properties
            // $new_obj->setTitle($this->getTitle() . ' ' . $lng->txt('copy_of_suffix'));
            $new_obj->setDescription($this->getDescription());
            $new_obj->setSubType($this->getSubType());
            $new_obj->setAPIAdapterName($this->getAPIAdapterName());
            $new_obj->setAPIFunctionsPrefix($this->getAPIFunctionsPrefix());
            $new_obj->setAutoReviewChar($this->getAutoReviewChar());
            $new_obj->setDefaultLessonMode($this->getDefaultLessonMode());
//        $new_obj->setEditable($this->getEditable());
            $new_obj->setMaxAttempt($this->getMaxAttempt());
            $new_obj->setModuleVersion($this->getModuleVersion());
            $new_obj->setModuleVersion(1);
            $new_obj->setCreditMode($this->getCreditMode());
            $new_obj->setAssignedGlossary($this->getAssignedGlossary());
            $new_obj->setTries($this->getTries());
            $new_obj->setSession($this->getSession());
            $new_obj->setNoMenu($this->getNoMenu());
            $new_obj->setHideNavig($this->getHideNavig());
            $new_obj->setFourth_edition($this->getFourth_edition());
            $new_obj->setSequencing($this->getSequencing());
            $new_obj->setInteractions($this->getInteractions());
            $new_obj->setObjectives($this->getObjectives());
            $new_obj->setComments($this->getComments());
            $new_obj->setTime_from_lms($this->getTime_from_lms());
            $new_obj->setDebug($this->getDebug());
            $new_obj->setLocalization($this->getLocalization());
//        $new_obj->setSequencingExpertMode(0); //$this->getSequencingExpertMode()
//        $new_obj->setDebugPw($this->getDebugPw());
            $new_obj->setOpenMode($this->getOpenMode());
            $new_obj->setWidth($this->getWidth());
            $new_obj->setHeight($this->getHeight());
            $new_obj->setAutoContinue($this->getAutoContinue());
            $new_obj->setAuto_last_visited($this->getAuto_last_visited());
            $new_obj->setCheck_values($this->getCheck_values());
//        $new_obj->setOfflineMode($this->getOfflineMode());
            $new_obj->setAutoSuspend($this->getAutoSuspend());
            $new_obj->setIe_force_render($this->getIe_force_render());
            $new_obj->setStyleSheetId($this->getStyleSheetId());
            $new_obj->update();


            // set/copy stylesheet
            /*		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                    $style_id = $this->getStyleSheetId();
                    if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id))
                    {
                        $style_obj = $ilias->obj_factory->getInstanceByObjId($style_id);
                        $new_id = $style_obj->ilClone();
                        $new_obj->setStyleSheetId($new_id);
                        $new_obj->update();
                    }*/

            // up to this point $new_obj is of type ilobjsahslearning module

            // create instance of correct subtype and call forward it to
            // cloneIntoNewObject method
            switch ($this->getSubType()) {
                case "scorm":
                    $source_obj = new ilObjSCORMLearningModule($this->getRefId());
                    $new_obj = new ilObjSCORMLearningModule($new_obj->getRefId());
                    break;

                case "scorm2004":
                    $source_obj = new ilObjSCORM2004LearningModule($this->getRefId());
                    $new_obj = new ilObjSCORM2004LearningModule($new_obj->getRefId());
                    break;
            }

            // copy data directory
            $new_obj->populateByDirectoy($source_obj->getDataDirectory());

//        // copy authored content ...
//        if ($new_obj->getEditable()) {
//            $source_obj->copyAuthoredContent($new_obj);
//        } else {
            // ... or read manifest file
            $new_obj->readObject();
//        }
            $obj_settings = new ilLPObjSettings($this->getId());
            $obj_settings->cloneSettings($new_obj->getId());
            /** @var ilScormLP $olp */
            $olp = ilObjectLP::getInstance($this->getId());
            $collection = $olp->getCollectionInstance();
            if ($collection) {
                $collection->cloneCollection($new_obj->getRefId(), $cp_options->getCopyId());
            }
        }
        return $new_obj;
    }
    
//    public function zipLmForOfflineMode()
//    {
//        $lmDir = ilUtil::getWebspaceDir("filesystem") . "/lm_data/lm_" . $this->getId();
//        $zipFile = ilUtil::getDataDir() . "/lm_data/lm_" . $this->getId();
//        return ilUtil::zip($lmDir, $zipFile, true);
//    }

    /**
     * Get cmi.core.student_id / cmi.learner_id for API
     */
    public function getApiStudentId() : string
    {
        global $DIC;
        $usr = $DIC->user();
        $idSetting = $this->getIdSetting();
        $studentId = (string) $usr->getId();
        if ($idSetting % 2 == 1) {
            $studentId = $usr->getLogin();
        }
        if ($idSetting > 3) {
            $studentId .= '_o_' . $this->getId();
        } elseif ($idSetting > 1) {
            $studentId .= '_r_' . $this->getRefId();
        }
        return $studentId;
    }

    /**
     * Get cmi.core.student_name / cmi.learner_name for API
     * note: 'lastname, firstname' is required for SCORM 1.2; 9 = no name to hide student_name for external content
     */
    public function getApiStudentName() : string
    {
        global $DIC;
        $lng = $DIC->language();
        $usr = $DIC->user();
        $studentName = " ";
        switch ($this->getNameSetting()) {
            case 0:
                $studentName = $usr->getLastname() . ', ' . $usr->getFirstname();
                break;
            case 1:
                $studentName = $usr->getFirstname() . ' ' . $usr->getLastname();
                break;
            case 2:
                $studentName = $usr->getFullname();
                break;
            case 3:
                switch ($usr->getGender()) {
                    case 'f':
                        $studentName = $lng->txt('salutation_f') . ' ';
                        break;

                    case 'm':
                        $studentName = $lng->txt('salutation_m') . ' ';
                        break;

                    case 'n':
                        $studentName = '';//$lng->txt('salutation_n');
                        break;

                    default:
                        $studentName = $lng->txt('salutation') . ' ';
                }
                $studentName .= $usr->getLastname();
                break;
            case 4:
                $studentName = $usr->getFirstname();
                break;
        }
        return $studentName;
    }

    //todo : replace LinkButton

    /**
     * get button for view
     */
    public function getViewButton() : ilLinkButton
    {
        $setUrl = "ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=" . $this->getRefID();
        // $setUrl = $this->getLinkTargetByClass("ilsahspresentationgui", "")."&amp;ref_id=".$this->getRefID();
        $setTarget = "ilContObj" . $this->getId();
        $om = $this->getOpenMode();
        $width = $this->getWidth();
        $height = $this->getHeight();
        if (($om == 5 || $om == 1) && $width > 0 && $height > 0) {
            $om++;
        }
        if ($om != 0) {
            $setUrl = "javascript:void(0); onclick=startSAHS('" . $setUrl . "','ilContObj" . $this->getId() . "'," . $om . "," . $width . "," . $height . ");";
            $setTarget = "";
        }
        $button = ilLinkButton::getInstance();
        $button->setCaption("view");
        $button->setPrimary(true);
        $button->setUrl($setUrl);
        $button->setTarget($setTarget);
        return $button;
    }
}
