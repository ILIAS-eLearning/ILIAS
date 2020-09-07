<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Exercise data set class
 *
 * Entities:
 *
 * - exc: Exercise data
 * - exc_assignment: Assignment data
 * - exc_crit_cat: criteria category
 * - exc_crit: criteria
 * - exc_ass_file_order: Order of instruction files
 * - exc_ass_reminders: Assingment reminder data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesExercise
 */
class ilExerciseDataSet extends ilDataSet
{
    /**
     * Get supported versions
     *
     * @param
     * @return
     */
    public function getSupportedVersions()
    {
        return array("4.1.0", "4.4.0", "5.0.0", "5.1.0", "5.2.0", "5.3.0");
    }
    
    /**
     * Get xml namespace
     *
     * @param
     * @return
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Exercise/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     *
     * @param
     * @return
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == "exc") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "PassMode" => "text",
                        "PassNr" => "integer",
                        "ShowSubmissions" => "integer"
                    );
                    
                case "4.4.0":
                case "5.0.0":
                case "5.1.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "PassMode" => "text",
                        "PassNr" => "integer",
                        "ShowSubmissions" => "integer",
                        "ComplBySubmission" => "integer"
                    );
                    
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "PassMode" => "text",
                        "PassNr" => "integer",
                        "ShowSubmissions" => "integer",
                        "ComplBySubmission" => "integer",
                        "Tfeedback" => "integer"
                    );
            }
        }

        if ($a_entity == "exc_assignment") {
            switch ($a_version) {
                case "4.1.0":
                    return array(
                        "Id" => "integer",
                        "ExerciseId" => "integer",
                        "Deadline" => "text",
                        "Instruction" => "text",
                        "Title" => "text",
                        "Mandatory" => "integer",
                        "OrderNr" => "integer",
                        "Dir" => "directory");
                    
                case "4.4.0":
                    return array(
                        "Id" => "integer",
                        "ExerciseId" => "integer",
                        "Type" => "integer",
                        "Deadline" => "integer",
                        "Instruction" => "text",
                        "Title" => "text",
                        "Mandatory" => "integer",
                        "OrderNr" => "integer",
                        "Dir" => "directory"
                        // peer
                        ,"Peer" => "integer"
                        ,"PeerMin" => "integer"
                        ,"PeerDeadline" => "integer"
                        // global feedback
                        ,"FeedbackFile" => "integer"
                        ,"FeedbackCron" => "integer"
                        ,"FeedbackDate" => "integer"
                        ,"FeedbackDir" => "directory"
                    );
                    
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "ExerciseId" => "integer",
                        "Type" => "integer",
                        "Deadline" => "integer",
                        "Instruction" => "text",
                        "Title" => "text",
                        "Mandatory" => "integer",
                        "OrderNr" => "integer",
                        "Dir" => "directory"
                        // peer
                        ,"Peer" => "integer"
                        ,"PeerMin" => "integer"
                        ,"PeerDeadline" => "integer"
                        ,"PeerFile" => "integer"
                        ,"PeerPersonal" => "integer"
                        // global feedback
                        ,"FeedbackFile" => "integer"
                        ,"FeedbackCron" => "integer"
                        ,"FeedbackDate" => "integer"
                        ,"FeedbackDir" => "directory"
                    );
                    
                case "5.1.0":
                case "5.2.0":
                    return array(
                        "Id" => "integer",
                        "ExerciseId" => "integer",
                        "Type" => "integer",
                        "Deadline" => "integer",
                        "Deadline2" => "integer",
                        "Instruction" => "text",
                        "Title" => "text",
                        "Mandatory" => "integer",
                        "OrderNr" => "integer",
                        "TeamTutor" => "integer",
                        "MaxFile" => "integer",
                        "Dir" => "directory"
                        // peer
                        ,"Peer" => "integer"
                        ,"PeerMin" => "integer"
                        ,"PeerDeadline" => "integer"
                        ,"PeerFile" => "integer"
                        ,"PeerPersonal" => "integer"
                        ,"PeerChar" => "integer"
                        ,"PeerUnlock" => "integer"
                        ,"PeerValid" => "integer"
                        ,"PeerText" => "integer"
                        ,"PeerRating" => "integer"
                        ,"PeerCritCat" => "integer"
                        // global feedback
                        ,"FeedbackFile" => "integer"
                        ,"FeedbackCron" => "integer"
                        ,"FeedbackDate" => "integer"
                        ,"FeedbackDir" => "directory"
                    );
                case "5.3.0":
                    return array(
                        "Id" => "integer",
                        "ExerciseId" => "integer",
                        "Type" => "integer",
                        "Deadline" => "integer",
                        "Deadline2" => "integer",
                        "Instruction" => "text",
                        "Title" => "text",
                        "Mandatory" => "integer",
                        "OrderNr" => "integer",
                        "TeamTutor" => "integer",
                        "MaxFile" => "integer",
                        "Dir" => "directory",
                        //web data directory
                        "WebDataDir" => "directory"
                        // peer
                        ,"Peer" => "integer"
                        ,"PeerMin" => "integer"
                        ,"PeerDeadline" => "integer"
                        ,"PeerFile" => "integer"
                        ,"PeerPersonal" => "integer"
                        ,"PeerChar" => "integer"
                        ,"PeerUnlock" => "integer"
                        ,"PeerValid" => "integer"
                        ,"PeerText" => "integer"
                        ,"PeerRating" => "integer"
                        ,"PeerCritCat" => "integer"
                        // global feedback
                        ,"FeedbackFile" => "integer"
                        ,"FeedbackCron" => "integer"
                        ,"FeedbackDate" => "integer"
                        ,"FeedbackDir" => "directory"
                        ,"FbDateCustom" => "integer"
                    );
            }
        }
        
        if ($a_entity == "exc_cit_cat") {
            switch ($a_version) {
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer"
                        ,"Parent" => "integer"
                        ,"Title" => "text"
                        ,"Pos" => "integer"
                    );
            }
        }

        if ($a_entity == "exc_cit") {
            switch ($a_version) {
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    return array(
                        "Id" => "integer"
                        ,"Parent" => "integer"
                        ,"Type" => "text"
                        ,"Title" => "text"
                        ,"Descr" => "text"
                        ,"Pos" => "integer"
                        ,"Required" => "integer"
                        ,"Def" => "text"
                        ,"DefJson" => "text"
                    );
            }
        }

        if ($a_entity == "exc_ass_file_order") {
            switch ($a_version) {
                case "5.3.0":
                    return array(
                    "Id" => "integer"
                    , "AssignmentId" => "integer"
                    , "Filename" => "text"
                    , "OrderNr" => "integer"
                    );
            }
        }

        if ($a_entity == "exc_ass_reminders") {
            switch ($a_version) {
                case "5.3.0":
                    return array(
                        "Type" => "text",
                        "AssignmentId" => "integer",
                        "ExerciseId" => "integer",
                        "Status" => "integer",
                        "Start" => "integer",
                        "End" => "integer",
                        "Frequency" => "integer",
                        "LastSend" => "integer",
                        "TemplateId" => "integer"
                    );
            }
        }
        return false;
    }

    /**
     * Read data
     *
     * @param
     * @return
     */
    public function readData($a_entity, $a_version, $a_ids, $a_field = "")
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
                
        if ($a_entity == "exc") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT exc_data.obj_id id, title, description," .
                        " pass_mode, pass_nr, show_submissions" .
                        " FROM exc_data JOIN object_data ON (exc_data.obj_id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("exc_data.obj_id", $a_ids, false, "integer"));
                    break;
                    
                case "4.4.0":
                case "5.0.0":
                case "5.1.0":
                    $this->getDirectDataFromQuery("SELECT exc_data.obj_id id, title, description," .
                        " pass_mode, pass_nr, show_submissions, compl_by_submission" .
                        " FROM exc_data JOIN object_data ON (exc_data.obj_id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("exc_data.obj_id", $a_ids, false, "integer"));
                    break;
                
                case "5.2.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT exc_data.obj_id id, title, description," .
                        " pass_mode, pass_nr, show_submissions, compl_by_submission, tfeedback" .
                        " FROM exc_data JOIN object_data ON (exc_data.obj_id = object_data.obj_id)" .
                        " WHERE " . $ilDB->in("exc_data.obj_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "exc_assignment") {
            switch ($a_version) {
                case "4.1.0":
                    $this->getDirectDataFromQuery("SELECT id, exc_id exercise_id, time_stamp deadline, " .
                        " instruction, title, start_time, mandatory, order_nr" .
                        " FROM exc_assignment" .
                        " WHERE " . $ilDB->in("exc_id", $a_ids, false, "integer"));
                    break;
                
                case "4.4.0":
                    $this->getDirectDataFromQuery("SELECT id, exc_id exercise_id, type, time_stamp deadline," .
                        " instruction, title, start_time, mandatory, order_nr, peer, peer_min, peer_dl peer_deadline," .
                        " fb_file feedback_file, fb_cron feedback_cron, fb_date feedback_date" .
                        " FROM exc_assignment" .
                        " WHERE " . $ilDB->in("exc_id", $a_ids, false, "integer"));
                    break;
                
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT id, exc_id exercise_id, type, time_stamp deadline," .
                        " instruction, title, start_time, mandatory, order_nr, peer, peer_min, peer_dl peer_deadline," .
                        " peer_file, peer_prsl peer_personal, fb_file feedback_file, fb_cron feedback_cron, fb_date feedback_date" .
                        " FROM exc_assignment" .
                        " WHERE " . $ilDB->in("exc_id", $a_ids, false, "integer"));
                    break;
                
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT id, exc_id exercise_id, type, time_stamp deadline, deadline2," .
                        " instruction, title, start_time, mandatory, order_nr, team_tutor, max_file, peer, peer_min," .
                        " peer_dl peer_deadline, peer_file, peer_prsl peer_personal, peer_char, peer_unlock, peer_valid," .
                        " peer_text, peer_rating, peer_crit_cat, fb_file feedback_file, fb_cron feedback_cron, fb_date feedback_date," .
                        " fb_date_custom" .
                        " FROM exc_assignment" .
                        " WHERE " . $ilDB->in("exc_id", $a_ids, false, "integer"));
                    break;
            }
        }
        
        if ($a_entity == "exc_crit_cat") {
            switch ($a_version) {
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT id, parent, title, pos" .
                        " FROM exc_crit_cat" .
                        " WHERE " . $ilDB->in("parent", $a_ids, false, "integer"));
                    break;
            }
        }
        
        if ($a_entity == "exc_crit") {
            switch ($a_version) {
                case "5.1.0":
                case "5.2.0":
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT id, parent, type, title" .
                        ", descr, pos, required, def" .
                        " FROM exc_crit" .
                        " WHERE " . $ilDB->in("parent", $a_ids, false, "integer"));
                    foreach ($this->data as $k => $v) {
                        $this->data[$k]["DefJson"] = "";
                        if ($v["Def"] != "") {
                            $this->data[$k]["DefJson"] = json_encode(unserialize($v["Def"]));
                        }
                    }
                    break;
            }
        }

        if ($a_entity == "exc_ass_file_order") {
            switch ($a_version) {
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT id, assignment_id, filename, order_nr" .
                        " FROM exc_ass_file_order" .
                        " WHERE " . $ilDB->in("assignment_id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity == "exc_ass_reminders") {
            switch ($a_version) {
                case "5.3.0":
                    $this->getDirectDataFromQuery("SELECT type, ass_id, exc_id, status, start, end, freq, last_send, template_id" .
                        " FROM exc_ass_reminders" .
                        " WHERE " . $ilDB->in("ass_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }

    /**
     * Get xml record (export)
     *
     * @param	array	abstract data record
     * @return	array	xml record
     */
    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        if ($a_entity == "exc_assignment") {
            // convert server dates to utc
            if ($a_set["StartTime"] != "") {
                $start = new ilDateTime($a_set["StartTime"], IL_CAL_UNIX);
                $a_set["StartTime"] = $start->get(IL_CAL_DATETIME, '', 'UTC');
            }
            if ($a_set["Deadline"] != "") {
                $deadline = new ilDateTime($a_set["Deadline"], IL_CAL_UNIX);
                $a_set["Deadline"] = $deadline->get(IL_CAL_DATETIME, '', 'UTC');
            }
            if ($a_set["Deadline2"] != "") {
                $deadline = new ilDateTime($a_set["Deadline2"], IL_CAL_UNIX);
                $a_set["Deadline2"] = $deadline->get(IL_CAL_DATETIME, '', 'UTC');
            }

            include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
            $fstorage = new ilFSStorageExercise($a_set["ExerciseId"], $a_set["Id"]);
            $a_set["Dir"] = $fstorage->getPath();
            
            include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
            $fstorage = new ilFSStorageExercise($a_set["ExerciseId"], $a_set["Id"]);
            $a_set["FeedbackDir"] = $fstorage->getGlobalFeedbackPath();

            //now the instruction files inside the root directory
            include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
            $fswebstorage = new ilFSWebStorageExercise($a_set['ExerciseId'], $a_set['Id']);
            $a_set['WebDataDir'] = $fswebstorage->getPath();
        }

        //Discuss if necessary when working with timestamps.
        if ($a_entity == "exc_ass_reminders") {
            if ($a_set["End"] != "") {
                $end = new ilDateTime($a_set["End"], IL_CAL_UNIX);
                $a_set["End"] = $end->get(IL_CAL_DATETIME, '', 'UTC');
            }
            if ($a_set["LastSend"] != "") {
                $last = new ilDateTime($a_set["LastSend"], IL_CAL_UNIX);
                $a_set["LastSend"] = $last->get(IL_CAL_DATETIME, '', 'UTC');
            }
        }

        return $a_set;
    }

    
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        switch ($a_entity) {
            case "exc":
                switch ($a_version) {
                    case "4.1.0":
                    case "4.4.0":
                    case "5.0.0":
                        return array(
                            "exc_assignment" => array("ids" => $a_rec["Id"])
                        );

                    case "5.1.0":
                    case "5.2.0":
                    case "5.3.0":
                        return array(
                            "exc_crit_cat" => array("ids" => $a_rec["Id"]),
                            "exc_assignment" => array("ids" => $a_rec["Id"])
                        );
                }
                break;

            case "exc_crit_cat":
                return array(
                    "exc_crit" => array("ids" => $a_rec["Id"])
                );

            case "exc_assignment":
                switch ($a_version) {
                    case "5.3.0":
                        return array(
                            "exc_ass_file_order" => array("ids" => $a_rec["Id"]),
                            "exc_ass_reminders" => array("ids" => $a_rec["Id"])
                        );

                }
                break;
        }
        return false;
    }
    
    
    /**
     * Import record
     *
     * @param
     * @return
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        //echo $a_entity;
        //var_dump($a_rec);

        switch ($a_entity) {
            case "exc":
                include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
                
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['Id'])) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjExercise();
                    $newObj->setType("exc");
                    $newObj->create(true);
                }
                
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setDescription($a_rec["Description"]);
                $newObj->setPassMode($a_rec["PassMode"]);
                $newObj->setPassNr($a_rec["PassNr"]);
                $newObj->setShowSubmissions($a_rec["ShowSubmissions"]);
                $newObj->setCompletionBySubmission($a_rec["ComplBySubmission"]);
                $newObj->setTutorFeedback($a_rec["Tfeedback"]);
                $newObj->update();
                $newObj->saveData();
                $this->current_exc = $newObj;

                $a_mapping->addMapping("Modules/Exercise", "exc", $a_rec["Id"], $newObj->getId());
                break;

            case "exc_assignment":
                $exc_id = $a_mapping->getMapping("Modules/Exercise", "exc", $a_rec["ExerciseId"]);
                if ($exc_id > 0) {
                    if (is_object($this->current_exc) && $this->current_exc->getId() == $exc_id) {
                        $exc = $this->current_exc;
                    } else {
                        include_once("./Modules/Exercise/classes/class.ilObjExercise.php");
                        $exc = new ilObjExercise($exc_id, false);
                    }

                    include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

                    $ass = new ilExAssignment();
                    $ass->setExerciseId($exc_id);
                    
                    if ($a_rec["StartTime"] != "") {
                        $start = new ilDateTime($a_rec["StartTime"], IL_CAL_DATETIME, "UTC");
                        $ass->setStartTime($start->get(IL_CAL_UNIX));
                    }

                    if ($a_rec["Deadline"] != "") {
                        $deadline = new ilDateTime($a_rec["Deadline"], IL_CAL_DATETIME, "UTC");
                        $ass->setDeadline($deadline->get(IL_CAL_UNIX));
                    }
                    
                    $ass->setInstruction($a_rec["Instruction"]);
                    $ass->setTitle($a_rec["Title"]);
                    $ass->setMandatory($a_rec["Mandatory"]);
                    $ass->setOrderNr($a_rec["OrderNr"]);
                    
                    // 4.2
                    $ass->setType($a_rec["Type"]);
                    
                    // 4.4
                    $ass->setPeerReview($a_rec["Peer"]);
                    $ass->setPeerReviewMin($a_rec["PeerMin"]);
                    $ass->setPeerReviewDeadline($a_rec["PeerDeadline"]);
                    $ass->setFeedbackFile($a_rec["FeedbackFile"]);
                    $ass->setFeedbackCron($a_rec["FeedbackCron"]);
                    $ass->setFeedbackDate($a_rec["FeedbackDate"]);
                    
                    // 5.0
                    $ass->setPeerReviewFileUpload($a_rec["PeerFile"]);
                    $ass->setPeerReviewPersonalized($a_rec["PeerPersonal"]);
                    
                    // 5.1
                    if ($a_rec["Deadline2"] != "") {
                        $deadline = new ilDateTime($a_rec["Deadline2"], IL_CAL_DATETIME, "UTC");
                        $ass->setExtendedDeadline($deadline->get(IL_CAL_UNIX));
                    }
                    $ass->setMaxFile($a_rec["MaxFile"]);
                    $ass->setTeamTutor($a_rec["TeamTutor"]);
                    $ass->setPeerReviewChars($a_rec["PeerChar"]);
                    $ass->setPeerReviewSimpleUnlock($a_rec["PeerUnlock"]);
                    $ass->setPeerReviewValid($a_rec["PeerValid"]);
                    $ass->setPeerReviewText($a_rec["PeerText"]);
                    $ass->setPeerReviewRating($a_rec["PeerRating"]);

                    // 5.3
                    $ass->setFeedbackDateCustom($a_rec["FbDateCustom"]);
                    
                    // criteria catalogue
                    if ($a_rec["PeerCritCat"]) {
                        $ass->setPeerReviewCriteriaCatalogue($a_mapping->getMapping("Modules/Exercise", "exc_crit_cat", $a_rec["PeerCritCat"]));
                    }
                                                            
                    $ass->save();

                    include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
                    $fstorage = new ilFSStorageExercise($exc_id, $ass->getId());
                    $fstorage->create();
                    
                    // assignment files
                    $dir = str_replace("..", "", $a_rec["Dir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = $fstorage->getPath();
                        ilUtil::rCopy($source_dir, $target_dir);
                    }
                    
                    // (4.4) global feedback file
                    $dir = str_replace("..", "", $a_rec["FeedbackDir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = $fstorage->getGlobalFeedbackPath();
                        ilUtil::rCopy($source_dir, $target_dir);
                    }

                    // (5.3) assignment files inside ILIAS
                    include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
                    $fwebstorage = new ilFSWebStorageExercise($exc_id, $ass->getId());
                    $fwebstorage->create();
                    $dir = str_replace("..", "", $a_rec["WebDataDir"]);
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = $fwebstorage->getPath();
                        ilUtil::rCopy($source_dir, $target_dir);
                    }

                    // 5.4 Team wiki assignment AR
                    if ($a_rec["Type"] == ilExAssignment::TYPE_WIKI_TEAM) {
                        $ar = new ilExAssWikiTeamAR();
                        $ar->setId($ass->getId());
                        $ar->setTemplateRefId(0);
                        $ar->setContainerRefId(0);
                        $ar->save();
                    }

                    $a_mapping->addMapping("Modules/Exercise", "exc_assignment", $a_rec["Id"], $ass->getId());
                }

                break;
                
            case "exc_crit_cat":
                $exc_id = $a_mapping->getMapping("Modules/Exercise", "exc", $a_rec["Parent"]);
                if ($exc_id > 0) {
                    include_once("./Modules/Exercise/classes/class.ilExcCriteriaCatalogue.php");
                    $crit_cat = new ilExcCriteriaCatalogue();
                    $crit_cat->setParent($exc_id);
                    $crit_cat->setTitle($a_rec["Title"]);
                    $crit_cat->setPosition($a_rec["Pos"]);
                    $crit_cat->save();
                    
                    $a_mapping->addMapping("Modules/Exercise", "exc_crit_cat", $a_rec["Id"], $crit_cat->getId());
                }
                break;
            
            case "exc_crit":
                $crit_cat_id = $a_mapping->getMapping("Modules/Exercise", "exc_crit_cat", $a_rec["Parent"]);
                if ($crit_cat_id > 0) {
                    include_once("./Modules/Exercise/classes/class.ilExcCriteria.php");
                    $crit = ilExcCriteria::getInstanceByType($a_rec["Type"]);
                    $crit->setParent($crit_cat_id);
                    $crit->setTitle($a_rec["Title"]);
                    $crit->setDescription($a_rec["Descr"]);
                    $crit->setPosition($a_rec["Pos"]);
                    $crit->setRequired($a_rec["Required"]);
                    $crit->importDefinition($a_rec["Def"], $a_rec["DefJson"]);
                    $crit->save();
                }
                break;

            case "exc_ass_file_order":

                $ass_id = $a_mapping->getMapping("Modules/Exercise", "exc_assignment", $a_rec["AssignmentId"]);
                if ($ass_id > 0) {
                    ilExAssignment::instructionFileInsertOrder($a_rec["Filename"], $ass_id, $a_rec["OrderNr"]);
                }
                break;

            case "exc_ass_reminders":
                // (5.3) reminders
                include_once("./Modules/Exercise/classes/class.ilExAssignmentReminder.php");
                $new_ass_id = $a_mapping->getMapping("Modules/Exercise", "exc_assignment", $a_rec["AssId"]);
                $new_exc_id = $a_mapping->getMapping('Modules/Exercise', 'exc', $a_rec['ExcId']);
                //always UTC timestamp in db.
                $end = new ilDateTime($a_rec["End"], IL_CAL_DATETIME, "UTC");
                $rmd = new ilExAssignmentReminder($new_exc_id, $new_ass_id, $a_rec["Type"]);
                $rmd->setReminderStatus($a_rec["Status"]);
                $rmd->setReminderStart($a_rec["Start"]);
                $rmd->setReminderEnd($end->get(IL_CAL_UNIX));
                $rmd->setReminderFrequency($a_rec["Freq"]);
                $rmd->setReminderLastSend($a_rec["LastSend"]);
                $rmd->setReminderMailTemplate($a_rec["TemplateId"]);
                $rmd->save();
        }
    }
}
