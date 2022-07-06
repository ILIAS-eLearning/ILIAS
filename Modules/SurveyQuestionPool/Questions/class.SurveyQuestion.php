<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Basic class for all survey question types
 * The SurveyQuestion class defines and encapsulates basic methods and attributes
 * for survey question types to be used for all parent classes.
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyQuestion
{
    protected ?int $original_id;
    protected \ILIAS\SurveyQuestionPool\Editing\EditSessionRepository $edit_manager;
    protected ilObjUser $user;
    protected ilDBInterface $db;
    public int $id;
    public string $title;
    public string $description;
    public int $owner;
    public string $author;
    public array $materials;
    public int $survey_id;
    public int $obj_id;
    public string $questiontext;
    public bool $obligatory;
    public ilLanguage $lng;
    public int $orientation;    // 0 = vertical, 1 = horizontal
    /** @var ilSurveyMaterial[] */
    public array $material;
    public bool $complete;
    protected array $cumulated;
    private array $arrData;         //  question data
    protected ilLogger $log;

    protected \ILIAS\SurveyQuestionPool\Export\ImportSessionRepository $import_manager;

    public function __construct(
        string $title = "",
        string $description = "",
        string $author = "",
        string $questiontext = "",
        int $owner = -1
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $this->lng = $lng;
        $this->complete = 0;
        $this->title = $title;
        $this->description = $description;
        $this->questiontext = $questiontext;
        $this->author = $author;
        $this->cumulated = array();
        if (!$this->author) {
            $this->author = $ilUser->fullname;
        }
        $this->owner = $owner;
        if ($this->owner === -1) {
            $this->owner = $ilUser->getId();
        }
        $this->id = -1;
        $this->survey_id = -1;
        $this->obligatory = 1;
        $this->orientation = 0;
        $this->materials = array();
        $this->material = array();
        $this->arrData = array();

        $this->log = ilLoggerFactory::getLogger('svy');
        $this->import_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->repo()
            ->import();

        $this->edit_manager = $DIC->surveyQuestionPool()
            ->internal()
            ->repo()
            ->editing();
    }

    public function setComplete(bool $a_complete) : void
    {
        $this->complete = $a_complete;
    }
    
    public function isComplete() : bool
    {
        return false;
    }

    public function questionTitleExists(
        string $title,
        int $questionpool_object = 0
    ) : bool {
        $ilDB = $this->db;
        
        $refwhere = "";
        if ($questionpool_object > 0) {
            $refwhere = sprintf(
                " AND obj_fi = %s",
                $ilDB->quote($questionpool_object, 'integer')
            );
        }
        $result = $ilDB->queryF(
            "SELECT question_id FROM svy_question WHERE title = %s$refwhere",
            array('text'),
            array($title)
        );
        return $result->numRows() > 0;
    }

    public function setTitle(string $title = "") : void
    {
        $this->title = $title;
    }

    public function setObligatory(bool $obligatory = true) : void
    {
        $this->obligatory = $obligatory;
    }

    public function setOrientation(int $orientation = 0) : void
    {
        $this->orientation = $orientation;
    }

    public function setId(int $id = -1) : void
    {
        $this->id = $id;
    }

    public function setSurveyId(int $id = -1) : void
    {
        $this->survey_id = $id;
    }

    public function setDescription(string $description = "") : void
    {
        $this->description = $description;
    }

    public function addMaterials(
        string $materials_file,
        string $materials_name = ""
    ) : void {
        if (empty($materials_name)) {
            $materials_name = $materials_file;
        }
        if ((!empty($materials_name)) && (!array_key_exists($materials_name, $this->materials))) {
            $this->materials[$materials_name] = $materials_file;
        }
    }

    /**
     * Uploads and adds a material
     */
    public function setMaterialsfile(
        string $materials_filename,
        string $materials_tempfilename = "",
        string $materials_name = ""
    ) : void {
        if (!empty($materials_filename)) {
            $materialspath = $this->getMaterialsPath();
            if (!file_exists($materialspath)) {
                ilFileUtils::makeDirParents($materialspath);
            }
            if (ilFileUtils::moveUploadedFile(
                $materials_tempfilename,
                $materials_filename,
                $materialspath . $materials_filename
            )) {
                print "image not uploaded!!!! ";
            } else {
                $this->addMaterials($materials_filename, $materials_name);
            }
        }
    }

    public function deleteMaterial(
        string $materials_name = ""
    ) : void {
        foreach ($this->materials as $key => $value) {
            if (strcmp($key, $materials_name) === 0) {
                if (file_exists($this->getMaterialsPath() . $value)) {
                    unlink($this->getMaterialsPath() . $value);
                }
                unset($this->materials[$key]);
            }
        }
    }

    /**
     * Deletes all materials uris
     * @todo check if unlink is necessary
     */
    public function flushMaterials() : void
    {
        $this->materials = array();
    }

    public function setAuthor(string $author = "") : void
    {
        $ilUser = $this->user;

        if (!$author) {
            $author = $ilUser->fullname;
        }
        $this->author = $author;
    }

    public function setQuestiontext(string $questiontext = "") : void
    {
        $this->questiontext = $questiontext;
    }

    /**
     * @param int $owner user id of owner
     */
    public function setOwner(int $owner = 0) : void
    {
        $this->owner = $owner;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getObligatory() : bool
    {
        return $this->obligatory;
    }

    public function getSurveyId() : int
    {
        return $this->survey_id;
    }

    /**
     * @return int 0 = vertical, 1 = horizontal
     */
    public function getOrientation() : int
    {
        switch ($this->orientation) {
            case 0:
            case 1:
            case 2:
                break;
            default:
                $this->orientation = 0;
                break;
        }
        return $this->orientation;
    }


    public function getDescription() : string
    {
        return $this->description;
    }

    public function getAuthor() : string
    {
        return $this->author;
    }

    public function getOwner() : int
    {
        return $this->owner;
    }

    public function getQuestiontext() : string
    {
        return $this->questiontext;
    }

    /**
     * Get the reference(?) id of the container object
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Set the reference(?) id of the container object
     */
    public function setObjId(int $obj_id = 0) : void
    {
        $this->obj_id = $obj_id;
    }

    public function duplicate(
        bool $for_survey = true,
        string $title = "",
        string $author = "",
        int $owner = 0,
        int $a_survey_id = 0
    ) : ?int {
        if ($this->getId() <= 0) {
            // The question has not been saved. It cannot be duplicated
            return null;
        }
        // duplicate the question in database
        $clone = $this;
        $original_id = $this->getId();
        $clone->setId(-1);
        if ($a_survey_id > 0) {
            $clone->setObjId($a_survey_id);
        }
        if ($title) {
            $clone->setTitle($title);
        }
        if ($author) {
            $clone->setAuthor($author);
        }
        if ($owner) {
            $clone->setOwner($owner);
        }
        if ($for_survey) {
            $clone->saveToDb($original_id);
        } else {
            $clone->saveToDb();
        }
        // duplicate the materials
        $clone->duplicateMaterials($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);
        return $clone->getId();
    }

    public function copyObject(
        int $target_questionpool,
        string $title = ""
    ) : ?int {
        if ($this->getId() <= 0) {
            // The question has not been saved. It cannot be copied
            return null;
        }
        $clone = $this;
        $original_id = self::_getOriginalId($this->getId(), false);
        $clone->setId(-1);
        $source_questionpool = $this->getObjId();
        $clone->setObjId($target_questionpool);
        if ($title) {
            $clone->setTitle($title);
        }
        
        $clone->saveToDb();

        // duplicate the materials
        $clone->duplicateMaterials($original_id);
        // copy XHTML media objects
        $clone->copyXHTMLMediaObjectsOfQuestion($original_id);
        return $clone->getId();
    }
    
    /**
     * Copy media object usages from other question
     */
    public function copyXHTMLMediaObjectsOfQuestion(
        int $a_q_id
    ) : void {
        $mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $a_q_id);
        foreach ($mobs as $mob) {
            ilObjMediaObject::_saveUsage($mob, "spl:html", $this->getId());
        }
    }
    
    /**
     * load question data into object
     * note: this base implementation only loads the material data
     */
    public function loadFromDb(int $question_id) : void
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT * FROM svy_material WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        $this->material = array();
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $mat = new ilSurveyMaterial();
                $mat->type = (string) $row['material_type'];
                $mat->internal_link = (string) $row['internal_link'];
                $mat->title = (string) $row['material_title'];
                $mat->import_id = (string) $row['import_id'];
                $mat->text_material = (string) $row['text_material'];
                $mat->external_link = (string) $row['external_link'];
                $mat->file_material = (string) $row['file_material'];
                $this->material[] = $mat;
            }
        }
    }

    /**
     * Checks whether the question is complete or not
     */
    public static function _isComplete(int $question_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT complete FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows()) {
            $row = $ilDB->fetchAssoc($result);
            if ((int) $row["complete"] === 1) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Saves the complete flag to the database
     */
    public function saveCompletionStatus(
        int $original_id = 0
    ) : void {
        $ilDB = $this->db;
        
        $question_id = $this->getId();
        if ($original_id > 0) {
            $question_id = $original_id;
        }

        if ($this->getId() > 0) {
            $this->log->debug("UPDATE svy_question question_id=" . $question_id);

            // update existing dataset
            $affectedRows = $ilDB->manipulateF(
                "UPDATE svy_question SET complete = %s, tstamp = %s WHERE question_id = %s",
                array('text', 'integer', 'integer'),
                array($this->isComplete(), time(), $question_id)
            );
        }
    }

    /**
     * Saves a SurveyQuestion object to a database
     */
    public function saveToDb(int $original_id = 0) : int
    {
        $ilDB = $this->db;
        
        // cleanup RTE images which are not inserted into the question text
        ilRTE::_cleanupMediaObjectUsage($this->getQuestiontext(), "spl:html", $this->getId());
        $affectedRows = 0;
        if ($this->getId() === -1) {
            // Write new dataset
            $next_id = $ilDB->nextId('svy_question');
            $affectedRows = $ilDB->insert("svy_question", array(
                "question_id" => array("integer", $next_id),
                "questiontype_fi" => array("integer", $this->getQuestionTypeID()),
                "obj_fi" => array("integer", $this->getObjId()),
                "owner_fi" => array("integer", $this->getOwner()),
                "title" => array("text", $this->getTitle()),
                "label" => array("text", (strlen($this->label)) ? $this->label : null),
                "description" => array("text", $this->getDescription()),
                "author" => array("text", $this->getAuthor()),
                "questiontext" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestiontext(), 0)),
                "obligatory" => array("text", $this->getObligatory()),
                "complete" => array("text", $this->isComplete()),
                "created" => array("integer", time()),
                "original_id" => array("integer", ($original_id) ?: null),
                "tstamp" => array("integer", time())
            ));

            //$this->log->debug("INSERT: svy_question id=".$next_id." questiontype_fi=".$this->getQuestionTypeID()." obj_fi".$this->getObjId()." title=".$this->getTitle()." ...");

            $this->setId($next_id);
        } else {
            // update existing dataset
            $affectedRows = $ilDB->update("svy_question", array(
                "title" => array("text", $this->getTitle()),
                "label" => array("text", (strlen($this->label)) ? $this->label : null),
                "description" => array("text", $this->getDescription()),
                "author" => array("text", $this->getAuthor()),
                "questiontext" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getQuestiontext(), 0)),
                "obligatory" => array("text", $this->getObligatory()),
                "complete" => array("text", $this->isComplete()),
                "tstamp" => array("integer", time())
            ), array(
            "question_id" => array("integer", $this->getId())
            ));

            $this->log->debug("UPDATE svy_question id=" . $this->getId() . " SET: title=" . $this->getTitle() . " ...");
        }
        return $affectedRows;
    }
    
    public function saveMaterial() : void
    {
        $ilDB = $this->db;
        
        $this->log->debug("DELETE: svy_material question_fi=" . $this->getId());

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_material WHERE question_fi = %s",
            array('integer'),
            array($this->getId())
        );
        ilInternalLink::_deleteAllLinksOfSource("sqst", $this->getId());

        foreach ($this->material as $material) {
            $next_id = $ilDB->nextId('svy_material');

            $this->log->debug("INSERT: svy_material question_fi=" . $this->getId());

            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_material " .
                "(material_id, question_fi, internal_link, import_id, material_title, tstamp," .
                "text_material, external_link, file_material, material_type) " .
                "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                array('integer','integer','text','text','text','integer','text','text','text','integer'),
                array(
                    $next_id, $this->getId(), $material->internal_link, $material->import_id,
                    $material->title, time(), $material->text_material, $material->external_link,
                    $material->file_material, $material->type)
            );
            if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $material->internal_link, $matches)) {
                ilInternalLink::_saveLink("sqst", $this->getId(), $matches[2], (int) $matches[3], (int) $matches[1]);
            }
        }
    }
    
    /**
     * Creates a new question with a 0 timestamp when a new question is created
     * This assures that an ID is given to the question if a file upload or something else occurs
     * @return int ID of the new question
     */
    public function createNewQuestion() : int
    {
        $ilDB = $this->db;
                
        $obj_id = $this->getObjId();
        if ($obj_id > 0) {
            $next_id = $ilDB->nextId('svy_question');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_question (question_id, questiontype_fi, " .
                "obj_fi, owner_fi, title, description, author, questiontext, obligatory, complete, " .
                "created, original_id, tstamp) VALUES " .
                "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                array('integer', 'integer', 'integer', 'integer', 'text', 'text', 'text', 'text',
                    'text', 'text', 'integer', 'integer', 'integer'),
                array(
                    $next_id,
                    $this->getQuestionTypeID(),
                    $obj_id,
                    $this->getOwner(),
                    null,
                    null,
                    $this->getAuthor(),
                    null,
                    "1",
                    "0",
                    time(),
                    null,
                    0
                )
            );
            $this->log->debug("INSERT INTO svy_question question_id= " . $next_id . " questiontype_fi= " . $this->getQuestionTypeID());

            $this->setId($next_id);
        }
        return $this->getId();
    }

    /**
     * Returns the image path for web accessible images of a question.
     */
    public function getImagePath() : string
    {
        return CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/images/";
    }

    /**
     * Returns the materials path for web accessible materials of a question.
     */
    public function getMaterialsPath() : string
    {
        return CLIENT_WEB_DIR . "/survey/$this->obj_id/$this->id/materials/";
    }

    /**
     * Returns the web image path for web accessible images of a question.
     */
    public function getImagePathWeb() : string
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/survey/$this->obj_id/$this->id/images/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
     * Returns the web image path for web accessable images of a question.
     */
    public function getMaterialsPathWeb() : string
    {
        $webdir = ilFileUtils::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/survey/$this->obj_id/$this->id/materials/";
        return str_replace(
            ilFileUtils::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH),
            ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH),
            $webdir
        );
    }

    /**
     * Saves a category to the database
     */
    public function saveCategoryToDb(
        string $categorytext,
        int $neutral = 0
    ) : int {
        $ilUser = $this->user;
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT title, category_id FROM svy_category WHERE title = %s AND neutral = %s AND owner_fi = %s",
            array('text','text','integer'),
            array($categorytext, $neutral, $ilUser->getId())
        );
        $insert = false;
        $returnvalue = "";
        $insert = true;
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                if (strcmp($row["title"], $categorytext) === 0) {
                    $returnvalue = $row["category_id"];
                    $insert = false;
                }
            }
        }
        if ($insert) {
            $next_id = $ilDB->nextId('svy_category');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_category (category_id, title, neutral, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s)",
                array('integer','text','text','integer','integer'),
                array($next_id, $categorytext, $neutral, $ilUser->getId(), time())
            );

            $this->log->debug("INSERT INTO svy_category id=" . $next_id);

            $returnvalue = $next_id;
        }
        return $returnvalue;
    }

    /**
     * Deletes datasets from the additional question table in the database
     */
    public function deleteAdditionalTableData(int $question_id) : void
    {
        $ilDB = $this->db;

        $this->log->debug("DELETE FROM " . $this->getAdditionalTableName());

        $ilDB->manipulateF(
            "DELETE FROM " . $this->getAdditionalTableName() . " WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
    }

    /**
     * Deletes a question and all materials from the database
     */
    public function delete(int $question_id) : void
    {
        $ilDB = $this->db;
        
        if ($question_id < 1) {
            return;
        }

        $result = $ilDB->queryF(
            "SELECT obj_fi FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            $obj_id = $row["obj_fi"];
        } else {
            return;
        }
        
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_answer WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_constraint WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $result = $ilDB->queryF(
            "SELECT constraint_fi FROM svy_qst_constraint WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        while ($row = $ilDB->fetchObject($result)) {
            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_constraint WHERE constraint_id = %s",
                array('integer'),
                array($row->constraint_fi)
            );
        }
    
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_qst_constraint WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_qblk_qst WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_svy_qst WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_variable WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );

        $this->deleteAdditionalTableData($question_id);
        
        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_material WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );

        $this->log->debug("SET OF DELETES svy_answer, svy_constraint, svy_qst_constraint, svy_qblk_qst, svy_qst_oblig, svy_svy_qst, svy_variable, svy_question, svy_material WHERE question_fi = " . $question_id);

        ilInternalLink::_deleteAllLinksOfSource("sqst", $question_id);

        $directory = CLIENT_WEB_DIR . "/survey/" . $obj_id . "/$question_id";
        if (preg_match("/\d+/", $obj_id) and preg_match("/\d+/", $question_id) and is_dir($directory)) {
            ilFileUtils::delDir($directory);
        }

        $mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $question_id);
        // remaining usages are not in text anymore -> delete them
        // and media objects (note: delete method of ilObjMediaObject
        // checks whether object is used in another context; if yes,
        // the object is not deleted!)
        foreach ($mobs as $mob) {
            ilObjMediaObject::_removeUsage($mob, "spl:html", $question_id);
            $mob_obj = new ilObjMediaObject($mob);
            $mob_obj->delete();
        }
        
        ilSurveySkill::handleQuestionDeletion($question_id, $obj_id);

        $this->log->debug("UPDATE svy_question");

        // #12772 - untie question copies from pool question
        $ilDB->manipulate("UPDATE svy_question" .
            " SET original_id = NULL" .
            " WHERE original_id  = " . $ilDB->quote($question_id, "integer"));
    }

    /**
     * Returns the question type of a question with a given id
     */
    public static function _getQuestionType(int $question_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($question_id < 1) {
            return "";
        }

        $result = $ilDB->queryF(
            "SELECT type_tag FROM svy_question, svy_qtype WHERE svy_question.question_id = %s AND svy_question.questiontype_fi = svy_qtype.questiontype_id",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $data = $ilDB->fetchAssoc($result);
            return $data["type_tag"];
        } else {
            return "";
        }
    }

    /**
     * Returns the question title of a question with a given id
     */
    public static function _getTitle(int $question_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            "SELECT title FROM svy_question WHERE svy_question.question_id = %s",
            array('integer'),
            array($question_id)
        );

        if ($data = $ilDB->fetchAssoc($result)) {
            return (string) $data["title"];
        }
        return "";
    }

    /**
     * Returns the original id of a question
     */
    public static function _getOriginalId(
        int $question_id,
        bool $a_return_question_id_if_no_original = true
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() > 0) {
            $row = $ilDB->fetchAssoc($result);
            if ($row["original_id"] > 0) {
                return (int) $row["original_id"];
            } elseif ($a_return_question_id_if_no_original) { // #12419
                return (int) $row["question_id"];
            }
        }
        return 0;
    }
    
    public function syncWithOriginal() : void
    {
        $ilDB = $this->db;
        
        if ($this->getOriginalId()) {
            $id = $this->getId();
            $original = $this->getOriginalId();

            $this->setId($this->getOriginalId());
            $this->setOriginalId(null);
            $this->saveToDb();

            $this->setId($id);
            $this->setOriginalId($original);

            $this->log->debug("DELETE FROM svy_material WHERE question_fi = " . $this->getOriginalId());

            $affectedRows = $ilDB->manipulateF(
                "DELETE FROM svy_material WHERE question_fi = %s",
                array('integer'),
                array($this->getOriginalId())
            );
            ilInternalLink::_deleteAllLinksOfSource("sqst", $this->original_id);
            if (strlen($this->material["internal_link"])) {
                $next_id = $ilDB->nextId('svy_material');
                $affectedRows = $ilDB->manipulateF(
                    "INSERT INTO svy_material (material_id, question_fi, internal_link, import_id, material_title, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
                    array('integer', 'integer', 'text', 'text', 'text', 'integer'),
                    array($next_id, $this->getOriginalId(), $this->material["internal_link"], $this->material["import_id"], $this->material["title"], time())
                );

                $this->log->debug("INSERT svy_material material_id=" . $next_id . " question_fi=" . $this->getOriginalId());

                if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $this->material["internal_link"], $matches)) {
                    ilInternalLink::_saveLink("sqst", $this->getOriginalId(), $matches[2], $matches[3], $matches[1]);
                }
            }
        }
    }

    /**
     * Returns a phrase title for phrase id
     */
    public function getPhrase(int $phrase_id) : string
    {
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT title FROM svy_phrase WHERE phrase_id = %s",
            array('integer'),
            array($phrase_id)
        );
        if ($row = $ilDB->fetchAssoc($result)) {
            return $row["title"];
        }
        return "";
    }

    /**
     * Returns true if the phrase title already exists for the current user(!)
     */
    public function phraseExists(string $title) : bool
    {
        $ilUser = $this->user;
        $ilDB = $this->db;
        
        $result = $ilDB->queryF(
            "SELECT phrase_id FROM svy_phrase WHERE title = %s AND owner_fi = %s",
            array('text', 'integer'),
            array($title, $ilUser->getId())
        );
        return $result->numRows() > 0;
    }

    public static function _questionExists(int $question_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if ($question_id < 1) {
            return false;
        }
        
        $result = $ilDB->queryF(
            "SELECT question_id FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        return $result->numRows() === 1;
    }

    public function addInternalLink(string $material_id) : void
    {
        $material_title = "";
        if ($material_id !== '') {
            if (preg_match("/il__(\w+)_(\d+)/", $material_id, $matches)) {
                $type = $matches[1];
                $target_id = $matches[2];
                $material_title = $this->lng->txt("obj_$type") . ": ";
                switch ($type) {
                    case "lm":
                        $cont_obj_gui = new ilObjContentObjectGUI("", $target_id, true);
                        $cont_obj = $cont_obj_gui->getObject();
                        $material_title .= $cont_obj->getTitle();
                        break;

                    case "pg":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        $cont_obj_gui = new ilObjLearningModuleGUI("", $lm_id, false);
                        /** @var ilObjLearningModule $cont_obj */
                        $cont_obj = $cont_obj_gui->getObject();
                        $pg_obj = new ilLMPageObject($cont_obj, $target_id);
                        $material_title .= $pg_obj->getTitle();
                        break;

                    case "st":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        $cont_obj_gui = new ilObjLearningModuleGUI("", $lm_id, false);
                        /** @var ilObjLearningModule $cont_obj */
                        $cont_obj = $cont_obj_gui->getObject();
                        $st_obj = new ilStructureObject($cont_obj, $target_id);
                        $material_title .= $st_obj->getTitle();
                        break;

                    case "git":
                        $material_title = $this->lng->txt("glossary_term") . ": " . ilGlossaryTerm::_lookGlossaryTerm($target_id);
                        break;
                    case "mob":
                        break;
                }
            }

            $mat = new ilSurveyMaterial();
            $mat->type = 0;
            $mat->internal_link = $material_id;
            $mat->title = $material_title;
            $this->addMaterial($mat);
            $this->saveMaterial();
        }
    }
    
    /**
     * @param array $a_array Array with indexes of the materials to delete
     */
    public function deleteMaterials(array $a_array) : void
    {
        foreach ($a_array as $idx) {
            unset($this->material[$idx]);
        }
        $this->material = array_values($this->material);
        $this->saveMaterial();
    }

    /**
     * Duplicates the materials of a question
     * @param int $question_id
     * @throws ilSurveyException
     */
    public function duplicateMaterials(int $question_id) : void
    {
        foreach ($this->materials as $filename) {
            $materialspath = $this->getMaterialsPath();
            $materialspath_original = preg_replace("/([^\d])$this->id([^\d])/", "\${1}$question_id\${2}", $materialspath);
            if (!file_exists($materialspath)) {
                ilFileUtils::makeDirParents($materialspath);
            }
            if (!copy($materialspath_original . $filename, $materialspath . $filename)) {
                throw new ilSurveyException("Unable to duplicate materials.");
            }
        }
    }
    
    public function addMaterial(ilSurveyMaterial $obj_material) : void
    {
        $this->material[] = $obj_material;
    }
    
    /**
     * Sets a material link for the question
     * @param string $material_id An internal link pointing to the material
     * @param bool $is_import A boolean indication that the internal link was imported from another ILIAS installation
     */
    public function setMaterial(
        string $material_id = "",
        bool $is_import = false,
        string $material_title = ""
    ) : void {
        if (strcmp($material_id, "") !== 0) {
            $import_id = "";
            if ($is_import) {
                $import_id = $material_id;
                $material_id = self::_resolveInternalLink($import_id);
            }
            if (strcmp($material_title, "") === 0) {
                if (preg_match("/il__(\w+)_(\d+)/", $material_id, $matches)) {
                    $type = $matches[1];
                    $target_id = $matches[2];
                    $material_title = $this->lng->txt("obj_$type") . ": ";
                    switch ($type) {
                        case "lm":
                            $cont_obj_gui = new ilObjContentObjectGUI("", $target_id, true);
                            $cont_obj = $cont_obj_gui->getObject();
                            $material_title .= $cont_obj->getTitle();
                            break;

                        case "pg":
                            $lm_id = ilLMObject::_lookupContObjID($target_id);
                            $cont_obj_gui = new ilObjLearningModuleGUI("", $lm_id, false);
                            /** @var ilObjLearningModule $cont_obj */
                            $cont_obj = $cont_obj_gui->getObject();
                            $pg_obj = new ilLMPageObject($cont_obj, $target_id);
                            $material_title .= $pg_obj->getTitle();
                            break;

                        case "st":
                            $lm_id = ilLMObject::_lookupContObjID($target_id);
                            $cont_obj_gui = new ilObjLearningModuleGUI("", $lm_id, false);
                            /** @var ilObjLearningModule $cont_obj */
                            $cont_obj = $cont_obj_gui->getObject();
                            $st_obj = new ilStructureObject($cont_obj, $target_id);
                            $material_title .= $st_obj->getTitle();
                            break;

                        case "git":
                            $material_title = $this->lng->txt("glossary_term") . ": " . ilGlossaryTerm::_lookGlossaryTerm($target_id);
                            break;
                        case "mob":
                            break;
                    }
                }
            }
            $this->material = array(
                "internal_link" => $material_id,
                "import_id" => $import_id,
                "title" => $material_title
            );
        }
        $this->saveMaterial();
    }
    
    public static function _resolveInternalLink(
        string $internal_link
    ) : string {
        $resolved_link = "";
        if (preg_match("/il_(\d+)_(\w+)_(\d+)/", $internal_link, $matches)) {
            switch ($matches[2]) {
                case "lm":
                    $resolved_link = ilLMObject::_getIdForImportId($internal_link);
                    break;
                case "pg":
                    $resolved_link = ilInternalLink::_getIdForImportId("PageObject", $internal_link);
                    break;
                case "st":
                    $resolved_link = ilInternalLink::_getIdForImportId("StructureObject", $internal_link);
                    break;
                case "git":
                    $resolved_link = ilInternalLink::_getIdForImportId("GlossaryItem", $internal_link);
                    break;
                case "mob":
                    $resolved_link = ilInternalLink::_getIdForImportId("MediaObject", $internal_link);
                    break;
            }
            if (strcmp($resolved_link, "") === 0) {
                $resolved_link = $internal_link;
            }
        } else {
            $resolved_link = $internal_link;
        }
        return $resolved_link;
    }
    
    public static function _resolveIntLinks(
        int $question_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        $resolvedlinks = 0;
        $result = $ilDB->queryF(
            "SELECT * FROM svy_material WHERE question_fi = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows()) {
            while ($row = $ilDB->fetchAssoc($result)) {
                $internal_link = $row["internal_link"];
                $resolved_link = self::_resolveInternalLink($internal_link);
                if (strcmp($internal_link, $resolved_link) !== 0) {
                    // internal link was resolved successfully
                    $affectedRows = $ilDB->manipulateF(
                        "UPDATE svy_material SET internal_link = %s, tstamp = %s WHERE material_id = %s",
                        array('text', 'integer', 'integer'),
                        array($resolved_link, time(), $row["material_id"])
                    );
                    $resolvedlinks++;
                }
            }
        }
        if ($resolvedlinks) {
            // there are resolved links -> reenter theses links to the database

            // delete all internal links from the database
            ilInternalLink::_deleteAllLinksOfSource("sqst", $question_id);

            $result = $ilDB->queryF(
                "SELECT * FROM svy_material WHERE question_fi = %s",
                array('integer'),
                array($question_id)
            );
            if ($result->numRows()) {
                while ($row = $ilDB->fetchAssoc($result)) {
                    if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $row["internal_link"], $matches)) {
                        ilInternalLink::_saveLink("sqst", $question_id, $matches[2], $matches[3], $matches[1]);
                    }
                }
            }
        }
    }
    
    public static function _getInternalLinkHref(
        string $target = "",
        int $a_parent_ref_id = null
    ) : string {
        $linktypes = array(
            "lm" => "LearningModule",
            "pg" => "PageObject",
            "st" => "StructureObject",
            "git" => "GlossaryItem",
            "mob" => "MediaObject"
        );
        $href = "";
        if (preg_match("/il__(\w+)_(\d+)/", $target, $matches)) {
            $type = $matches[1];
            $target_id = $matches[2];
            switch ($linktypes[$matches[1]]) {
                case "StructureObject":
                case "PageObject":
                case "GlossaryItem":
                case "LearningModule":
                    $href = ilFileUtils::removeTrailingPathSeparators(ILIAS_HTTP_PATH) . "/goto.php?target=" . $type . "_" . $target_id;
                    break;
                case "MediaObject":
                    $href = ilFileUtils::removeTrailingPathSeparators(
                        ILIAS_HTTP_PATH
                    ) . "/ilias.php?baseClass=ilLMPresentationGUI&obj_type=" . $linktypes[$type] . "&cmd=media&ref_id=" . $a_parent_ref_id . "&mob_id=" . $target_id;
                    break;
            }
        }
        return $href;
    }
    
    /**
     * is question writeable by a certain user
     */
    public static function _isWriteable(
        int $question_id,
        int $user_id
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if (($question_id < 1) || ($user_id < 1)) {
            return false;
        }
        
        $result = $ilDB->queryF(
            "SELECT obj_fi FROM svy_question WHERE question_id = %s",
            array('integer'),
            array($question_id)
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            $qpl_object_id = $row["obj_fi"];
            return ilObjSurveyQuestionPool::_isWriteable($qpl_object_id);
        }

        return false;
    }

    public function getQuestionTypeID() : int
    {
        $ilDB = $this->db;
        $result = $ilDB->queryF(
            "SELECT questiontype_id FROM svy_qtype WHERE type_tag = %s",
            array('text'),
            array($this->getQuestionType())
        );
        if ($result->numRows() === 1) {
            $row = $ilDB->fetchAssoc($result);
            return (int) $row["questiontype_id"];
        }

        return 0;
    }

    public function getQuestionType() : string
    {
        return "";
    }

    /**
     * Include the php class file for a given question type
     * @param int $gui 0 if the class should be included, 1 if the GUI class should be included
     */
    public static function _includeClass(
        string $question_type,
        int $gui = 0
    ) : bool {
        $type = $question_type;
        if ($gui === 1) {
            $type .= "GUI";
        } elseif ($gui === 2) {
            $type .= "Evaluation";
        }
        if (file_exists("./Modules/SurveyQuestionPool/Questions/class." . $type . ".php")) {
            return true;
        } else {
            global $DIC;

            $component_factory = $DIC["component.factory"];
            foreach ($component_factory->getActivePluginsInSlot("svyq") as $pl) {
                if (strcmp($pl->getQuestionType(), $question_type) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return the translation for a given question type
     * @param string $type_tag type of the question type
     */
    public static function _getQuestionTypeName(
        string $type_tag
    ) : string {
        global $DIC;

        if (file_exists("./Modules/SurveyQuestionPool/Questions/class." . $type_tag . ".php")) {
            $lng = $DIC->language();
            return $lng->txt($type_tag);
        } else {
            $component_factory = $DIC["component.factory"];
            foreach ($component_factory->getActivePluginsInSlot("svyq") as $pl) {
                if (strcmp($pl->getQuestionType(), $type_tag) === 0) {
                    return $pl->getQuestionTypeTranslation();
                }
            }
        }
        return "";
    }

    
    /**
     * Get question object
     */
    public static function _instanciateQuestion(int $question_id) : ?SurveyQuestion
    {
        $question_type = self::_getQuestionType($question_id);
        if ($question_type) {
            self::_includeClass($question_type);
            $question = new $question_type();
            $question->loadFromDb($question_id);
            return $question;
        }
        return null;
    }

    /**
     * Get question gui object
     */
    public static function _instanciateQuestionGUI(
        int $question_id
    ) : ?SurveyQuestionGUI {
        $question_type = self::_getQuestionType($question_id);
        if ($question_type) {
            self::_includeClass($question_type, 1);
            $guitype = $question_type . "GUI";
            $question = new $guitype($question_id);
            return $question;
        }
        return null;
    }

    public static function _instanciateQuestionEvaluation(
        int $question_id,
        array $a_finished_ids = null
    ) : ?SurveyQuestionEvaluation {
        $question = self::_instanciateQuestion($question_id);
        if (is_null($a_finished_ids)) {
            $a_finished_ids = [];
        }
        if ($question) {
            $question_type = self::_getQuestionType($question_id);
            self::_includeClass($question_type, 2);
            $class = $question_type . "Evaluation";
            $ev = new $class($question, $a_finished_ids);
            return $ev;
        }
        return null;
    }

    /**
     * @todo move to manager
     */
    public function isHTML(string $a_text) : bool
    {
        if (preg_match("/<[^>]*?>/", $a_text)) {
            return true;
        }

        return false;
    }
    
    /**
     * Reads an QTI material tag an creates a text string
     */
    public function QTIMaterialToString(ilQTIMaterial $a_material) : string
    {
        $svy_log = ilLoggerFactory::getLogger("svy");
        $svy_log->debug("material count: " . $a_material->getMaterialCount());

        $result = "";
        for ($i = 0; $i < $a_material->getMaterialCount(); $i++) {
            $material = $a_material->getMaterial($i);
            if (strcmp($material["type"], "mattext") === 0) {
                $result .= $material["material"]->getContent();
            }
            if (strcmp($material["type"], "matimage") === 0) {
                $matimage = $material["material"];
                if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches)) {
                    // import an mediaobject which was inserted using tiny mce
                    $this->import_manager->addMob(
                        $matimage->getLabel(),
                        $matimage->getUri()
                    );
                }
            }
        }
        return $result;
    }
    
    /**
     * Creates an XML material tag from a plain text or xhtml text
     */
    public function addMaterialTag(
        ilXmlWriter $a_xml_writer,
        string $a_material,
        bool $close_material_tag = true,
        bool $add_mobs = true,
        ?array $a_attrs = null
    ) : void {
        $a_xml_writer->xmlStartTag("material");
        $attrs = array(
            "type" => "text/plain"
        );
        if ($this->isHTML($a_material)) {
            $attrs["type"] = "text/xhtml";
        }
        if (is_array($a_attrs)) {
            $attrs = array_merge($attrs, $a_attrs);
        }
        $a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));

        if ($add_mobs) {
            $mobs = ilObjMediaObject::_getMobsOfObject("spl:html", $this->getId());
            foreach ($mobs as $mob) {
                $mob_obj = new ilObjMediaObject($mob);
                $imgattrs = array(
                    "label" => "il_" . IL_INST_ID . "_mob_" . $mob,
                    "uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle(),
                    "type" => "spl:html",
                    "id" => $this->getId()
                );
                $a_xml_writer->xmlElement("matimage", $imgattrs, null);
            }
        }
        if ($close_material_tag) {
            $a_xml_writer->xmlEndTag("material");
        }
    }

    /**
     * Prepares string for a text area output in surveys
     */
    public function prepareTextareaOutput(
        string $txt_output,
        bool $prepare_for_latex_output = false
    ) : string {
        return ilLegacyFormElementsUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output);
    }

    /**
     * Returns the question data
     */
    public function getQuestionDataArray(int $id) : array
    {
        return array();
    }

    /**
     * Creates the user data of the svy_answer table from the POST data
     * @return array User data according to the svy_answer table
     */
    public function getWorkingDataFromUserInput(array $post_data) : array
    {
        // overwrite in inherited classes
        $data = array();
        return $data;
    }
    
    /**
     * Import additional meta data from the question import file. Usually
     * the meta data section is used to store question elements which are not
     * part of the standard XML schema.
     */
    public function importAdditionalMetadata(array $a_meta) : void
    {
        // overwrite in inherited classes
    }
    
    /**
     * Import response data from the question import file
     */
    public function importResponses(array $a_data) : void
    {
        // overwrite in inherited classes
    }

    /**
     * Import bipolar adjectives from the question import file
     */
    public function importAdjectives(array $a_data) : void
    {
        // overwrite in inherited classes
    }

    /**
     * Import matrix rows from the question import file
     */
    public function importMatrix(array $a_data) : void
    {
        // overwrite in inherited classes
    }

    /**
     * Returns if the question is usable for preconditions
     */
    public function usableForPrecondition() : bool
    {
        // overwrite in inherited classes
        return false;
    }

    /**
     * Returns the available relations for the question
     */
    public function getAvailableRelations() : array
    {
        // overwrite in inherited classes
        return array();
    }

    /**
     * Returns the options for preconditions
     */
    public function getPreconditionOptions() : array
    {
        // overwrite in inherited classes
        return [];
    }
    
    /**
     * Returns the output for a precondition value
     * @param string $value The precondition value
     * @return string The output of the precondition value
     */
    public function getPreconditionValueOutput(string $value) : string
    {
        // overwrite in inherited classes
        return $value;
    }

    /**
     * Creates a form property for the precondition value
     */
    public function getPreconditionSelectValue(
        string $default,
        string $title,
        string $variable
    ) : ?ilFormPropertyGUI {
        // overwrite in inherited classes
        return null;
    }

    public function setOriginalId(?int $original_id) : void
    {
        $this->original_id = $original_id;
    }
    
    public function getOriginalId() : ?int
    {
        return $this->original_id;
    }
    
    public function getMaterial() : array
    {
        return $this->material;
    }
    
    public function setSubtype(int $a_subtype) : void
    {
        // do nothing
    }

    public function getSubtype() : ?int
    {
        // do nothing
        return null;
    }

    public function __get(string $value) : ?string
    {
        switch ($value) {
            default:
                if (array_key_exists($value, $this->arrData)) {
                    return (string) $this->arrData[$value];
                }

                return null;
        }
    }

    public function __set(string $key, string $value) : void
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }

    /**
     * Change original id of existing question in db
     */
    public static function _changeOriginalId(
        int $a_question_id,
        int $a_original_id,
        int $a_object_id
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate("UPDATE svy_question" .
            " SET original_id = " . $ilDB->quote($a_original_id, "integer") . "," .
            " obj_fi = " . $ilDB->quote($a_object_id, "integer") .
            " WHERE question_id = " . $ilDB->quote($a_question_id, "integer"));
    }
    
    public function getCopyIds(
        bool $a_group_by_survey = false
    ) : array {
        $ilDB = $this->db;
        
        $set = $ilDB->query("SELECT q.question_id,s.obj_fi" .
            " FROM svy_question q" .
            " JOIN svy_svy_qst sq ON (sq.question_fi = q.question_id)" .
            " JOIN svy_svy s ON (s.survey_id = sq.survey_fi)" .
            " WHERE original_id = " . $ilDB->quote($this->getId(), "integer"));
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$a_group_by_survey) {
                $res[] = (int) $row["question_id"];
            } else {
                $res[$row["obj_fi"]][] = (int) $row["question_id"];
            }
        }
        return $res;
    }
    
    public function hasCopies() : bool
    {
        return (bool) count($this->getCopyIds());
    }
    
    public static function _lookupSurveyObjId(
        int $a_question_id
    ) : ?int {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT svy_svy.obj_fi FROM svy_svy_qst" .
            " JOIN svy_svy ON (svy_svy.survey_id = svy_svy_qst.survey_fi)" .
            " WHERE svy_svy_qst.question_fi = " . $ilDB->quote($a_question_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($ilDB->numRows($set)) {
            return (int) $row["obj_fi"];
        }
        return null;
    }

    public static function lookupObjFi(
        int $a_qid
    ) : ?int {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT obj_fi FROM svy_question " .
            " WHERE question_id = " . $ilDB->quote($a_qid, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["obj_fi"];
        }
        return null;
    }

    /**
     * Strip slashes with add space fallback, see https://mantis.ilias.de/view.php?id=19727
     *                                        and https://mantis.ilias.de/view.php?id=24200
     */
    public function stripSlashesAddSpaceFallback(string $a_str) : string
    {
        $str = ilUtil::stripSlashes($a_str);
        if ($str !== $a_str) {
            $str = ilUtil::stripSlashes(str_replace("<", "< ", $a_str));
        }
        return $str;
    }

    /**
     * Get max sum score for specific survey (and this question type)
     */
    public static function getMaxSumScore(int $survey_id) : int
    {
        return 0;
    }
}
