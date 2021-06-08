<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Poll Dataset class
 *
 * This class implements the following entities:
 * - poll: object data
 * - poll_answer: data from table il_poll_answer
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPollDataSet extends ilDataSet
{
    protected $current_blog;
    
    public function getSupportedVersions()
    {
        return array("4.3.0", "5.0.0");
    }
    
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return "http://www.ilias.de/xml/Modules/Poll/" . $a_entity;
    }
    
    /**
     * Get field types for entity
     */
    protected function getTypes($a_entity, $a_version) : array
    {
        if ($a_entity == "poll") {
            switch ($a_version) {
                case "4.3.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Question" => "text",
                        "Image" => "text",
                        "ViewResults" => "integer",
                        "Dir" => "directory"
                        );
                    break;
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "Title" => "text",
                        "Description" => "text",
                        "Question" => "text",
                        "Image" => "text",
                        "ViewResults" => "integer",
                        "Dir" => "directory",
                        "ShowResultsAs" => "integer",
                        "ShowComments" => "integer",
                        "MaxAnswers" => "integer",
                        "ResultSort" => "integer",
                        "NonAnon" => "integer",
                        "Period" => "integer",
                        "PeriodBegin" => "integer",
                        "PeriodEnd" => "integer"

                    );
                break;
            }
        }
        
        if ($a_entity == "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                    return array(
                        "Id" => "integer",
                        "PollId" => "integer",
                        "Answer" => "text",
                        "Pos" => "integer",
                    );
                    break;
            }
        }

        return array();
    }

    public function readData($a_entity, $a_version, $a_ids, $a_field = "") : void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        
        if ($a_entity == "poll") {
            switch ($a_version) {
                case "4.3.0":
                    $this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description," .
                        "pl.question,pl.image,pl.view_results" .
                        " FROM il_poll pl" .
                        " JOIN object_data od ON (od.obj_id = pl.id)" .
                        " WHERE " . $ilDB->in("pl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("poll", "text"));
                    break;
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT pl.id,od.title,od.description" .
                        ",pl.question,pl.image,pl.view_results,pl.show_results_as" .
                        ",pl.max_answers,pl.result_sort,pl.non_anon,pl.period,pl.period_begin,pl.period_end" .
                        " FROM il_poll pl" .
                        " JOIN object_data od ON (od.obj_id = pl.id)" .
                        " WHERE " . $ilDB->in("pl.id", $a_ids, false, "integer") .
                        " AND od.type = " . $ilDB->quote("poll", "text"));
                    break;

            }
        }

        if ($a_entity == "poll_answer") {
            switch ($a_version) {
                case "4.3.0":
                case "5.0.0":
                    $this->getDirectDataFromQuery("SELECT id,poll_id,answer,pos" .
                        " FROM il_poll_answer WHERE " .
                        $ilDB->in("poll_id", $a_ids, false, "integer"));
                    break;
            }
        }
    }
    
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        switch ($a_entity) {
            case "poll":
                return array(
                    "poll_answer" => array("ids" => $a_rec["Id"])
                );
        }
        return false;
    }

    public function getXmlRecord($a_entity, $a_version, $a_set)
    {
        if ($a_entity == "poll") {
            $dir = ilObjPoll::initStorage($a_set["Id"]);
            $a_set["Dir"] = $dir;
            
            $a_set["ShowComments"] = ilNote::commentsActivated($a_set["Id"], 0, "poll");
        }

        return $a_set;
    }

    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version) : void
    {
        switch ($a_entity) {
            case "poll":
                // container copy
                if ($new_id = $a_mapping->getMapping("Services/Container", "objs", (int) ($a_rec["Id"] ?? 0))) {
                    $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $newObj = new ilObjPoll();
                    $newObj->create();
                }
                    
                $newObj->setTitle((string) ($a_rec["Title"] ?? ''));
                $newObj->setDescription((string) ($a_rec["Description"]));
                if ((int) $a_rec["MaxAnswers"]) {
                    $newObj->setMaxNumberOfAnswers((int) $a_rec["MaxAnswers"]);
                }
                $newObj->setSortResultByVotes((bool) ($a_rec["ResultSort"] ?? false));
                $newObj->setNonAnonymous((bool) ($a_rec["NonAnon"] ?? false));
                if ((int) $a_rec["ShowResultsAs"]) {
                    $newObj->setShowResultsAs((int) $a_rec["ShowResultsAs"]);
                }
                $newObj->setShowComments((bool) ($a_rec["ShowComments"] ?? false));
                $newObj->setQuestion((string) ($a_rec["Question"] ?? ''));
                $newObj->setImage((string) ($a_rec["Image"] ?? ''));
                $newObj->setViewResults((int) ($a_rec["ViewResults"] ?? ilObjPoll::VIEW_RESULTS_AFTER_VOTE));
                $newObj->setVotingPeriod((int) ($a_rec["Period"] ?? 0));
                $newObj->setVotingPeriodBegin((int) ($a_rec["PeriodBegin"] ?? 0));
                $newObj->setVotingPeriodEnd((int) ($a_rec["PeriodEnd"] ?? 0));
                $newObj->update();
                
                // handle image(s)
                if ($a_rec["Image"]) {
                    $dir = str_replace("..", "", (string) ($a_rec["Dir"] ?? ''));
                    if ($dir != "" && $this->getImportDirectory() != "") {
                        $source_dir = $this->getImportDirectory() . "/" . $dir;
                        $target_dir = ilObjPoll::initStorage($newObj->getId());
                        ilUtil::rCopy($source_dir, $target_dir);
                    }
                }

                $a_mapping->addMapping("Modules/Poll", "poll", (int) ($a_rec["Id"] ?? 0), $newObj->getId());
                break;

            case "poll_answer":
                $poll_id = (int) $a_mapping->getMapping("Modules/Poll", "poll", (int) ($a_rec["PollId"] ?? 0));
                if ($poll_id) {
                    $poll = new ilObjPoll($poll_id, false);
                    $poll->saveAnswer((string) ($a_rec["Answer"] ?? ''), (int) ($a_rec["pos"] ?? 10));
                }
                break;
        }
    }
}
