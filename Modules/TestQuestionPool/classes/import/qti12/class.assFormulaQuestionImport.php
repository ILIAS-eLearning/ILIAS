<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/TestQuestionPool/classes/import/qti12/class.assQuestionImport.php";

/**
* Class for formula question imports
*
* assFormulaQuestionImport is a class for formula question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id: class.assFormulaQuestionImport.php 1185 2010-02-02 08:36:26Z hschottm $
* @ingroup ModulesTestQuestionPool
*/
class assFormulaQuestionImport extends assQuestionImport
{
    /**
    * Creates a question from a QTI file
    *
    * Receives parameters from a QTI parser and creates a valid ILIAS question object
    *
    * @param object $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    * @access public
    */
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        // empty session variable for imported xhtml mobs
        unset($_SESSION["import_mob_xhtml"]);
        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

        $feedbacksgeneric = array();

        $this->object->setTitle($item->getTitle());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
        if (preg_match_all("/(\\\$v\\d+)/ims", $this->object->getQuestion(), $matches)) {
            foreach ($matches[1] as $variable) {
                $data = unserialize($item->getMetadataEntry($variable));
                $unit = $this->object->getUnitRepository()->getUnit($data["unitvalue"]);
                require_once 'Modules/TestQuestionPool/classes/class.assFormulaQuestionVariable.php';
                $varObj = new assFormulaQuestionVariable($variable, $data["rangemin"], $data["rangemax"], $unit, $data["precision"], $data["intprecision"]);
                $this->object->addVariable($varObj);
            }
        }
        if (preg_match_all("/(\\\$r\\d+)/ims", $this->object->getQuestion(), $rmatches)) {
            foreach ($rmatches[1] as $result) {
                $data = unserialize($item->getMetadataEntry($result));
                $unit = $this->object->getUnitRepository()->getUnit($data["unitvalue"]);
                require_once 'Modules/TestQuestionPool/classes/class.assFormulaQuestionResult.php';
                if (!is_array($data["rating"])) {
                    $resObj = new assFormulaQuestionResult($result, $data["rangemin"], $data["rangemax"], $data["tolerance"], $unit, $data["formula"], $data["points"], $data["precision"], true);
                } else {
                    $resObj = new assFormulaQuestionResult($result, $data["rangemin"], $data["rangemax"], $data["tolerance"], $unit, $data["formula"], $data["points"], $data["precision"], false, $data["rating"]["sign"], $data["rating"]["value"], $data["rating"]["unit"]);
                }
                $this->object->addResult($resObj);
                if (is_array($data["resultunits"])) {
                    foreach ($data["resultunits"] as $resu) {
                        $ru = $this->object->getUnitRepository()->getUnit($resu["unitvalue"]);
                        if (is_object($ru)) {
                            $this->object->addResultUnit($resObj, $ru);
                        }
                    }
                }
            }
        }
        $this->object->setPoints($item->getMetadataEntry("points"));
        $this->addGeneralMetadata($item);
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();
        // handle the import of media objects in XHTML code
        $questiontext = $this->object->getQuestion();
        $feedbacksgeneric = $this->getFeedbackGeneric($item);
        
        if (is_array($_SESSION["import_mob_xhtml"])) {
            include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
            include_once "./Services/RTE/classes/class.ilRTE.php";
            foreach ($_SESSION["import_mob_xhtml"] as $mob) {
                if ($tst_id > 0) {
                    include_once "./Modules/Test/classes/class.ilObjTest.php";
                    $importfile = ilObjTest::_getImportDirectory() . "/" . $_SESSION["tst_import_subdir"] . "/" . $mob["uri"];
                } else {
                    include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
                    $importfile = ilObjQuestionPool::_getImportDirectory() . "/" . $_SESSION["qpl_import_subdir"] . "/" . $mob["uri"];
                }
                $media_object = &ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
                ilObjMediaObject::_saveUsage($media_object->getId(), "qpl:html", $this->object->getId());
                $questiontext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $questiontext);
                
                foreach ($feedbacksgeneric as $correctness => $material) {
                    $feedbacksgeneric[$correctness] = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $material);
                }
            }
        }
        $this->object->setQuestion(ilRTE::_replaceMediaObjectImageSrc($questiontext, 1));
        
        foreach ($feedbacksgeneric as $correctness => $material) {
            $this->object->feedbackOBJ->importGenericFeedback(
                $this->object->getId(),
                $correctness,
                ilRTE::_replaceMediaObjectImageSrc($material, 1)
            );
        }

        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        
        $this->object->saveToDb();
        if (count($item->suggested_solutions)) {
            foreach ($item->suggested_solutions as $suggested_solution) {
                $this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
            }
            $this->object->saveToDb();
        }
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
    }
}
