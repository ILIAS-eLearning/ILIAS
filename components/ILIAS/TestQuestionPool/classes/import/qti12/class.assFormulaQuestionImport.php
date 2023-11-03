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
    * @param ilQtiItem $item The QTI item object
    * @param integer $questionpool_id The id of the parent questionpool
    * @param integer $tst_id The id of the parent test if the question is part of a test
    * @param object $tst_object A reference to the parent test object
    * @param integer $question_counter A reference to a question counter to count the questions of an imported question pool
    * @param array $import_mapping An array containing references to included ILIAS objects
    * @access public
    */
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, $import_mapping): array
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        // empty session variable for imported xhtml mobs
        ilSession::clear('import_mob_xhtml');

        $presentation = $item->getPresentation();
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

        $feedbacksgeneric = array();

        $this->object->setTitle($item->getTitle());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        if (preg_match_all("/(\\\$v\\d+)/ims", $this->object->getQuestion(), $matches)) {
            foreach ($matches[1] as $variable) {
                $data = unserialize($item->getMetadataEntry($variable), ["allowed_classes" => false]);
                $unit = $this->object->getUnitRepository()->getUnit((int) $data["unitvalue"]);
                $varObj = new assFormulaQuestionVariable($variable, $data["rangemin"], $data["rangemax"], $unit, $data["precision"], $data["intprecision"]);
                $this->object->addVariable($varObj);
            }
        }
        if (preg_match_all("/(\\\$r\\d+)/ims", $this->object->getQuestion(), $rmatches)) {
            foreach ($rmatches[1] as $result) {
                $data = unserialize($item->getMetadataEntry($result), ["allowed_classes" => false]);
                $unit = $this->object->getUnitRepository()->getUnit((int) $data["unitvalue"]);
                if (!is_array($data["rating"])) {
                    $resObj = new assFormulaQuestionResult($result, $data["rangemin"], $data["rangemax"], $data["tolerance"], $unit, $data["formula"], $data["points"], $data["precision"], true);
                } else {
                    $resObj = new assFormulaQuestionResult($result, $data["rangemin"], $data["rangemax"], $data["tolerance"], $unit, $data["formula"], $data["points"], $data["precision"], false, $data["rating"]["sign"], $data["rating"]["value"], $data["rating"]["unit"]);
                }
                if (array_key_exists('resulttype', $data)) {
                    $resObj->setResultType($data["resulttype"]);
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

        if (is_array(ilSession::get("import_mob_xhtml"))) {
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                if ($tst_id > 0) {
                    $importfile = ilObjTest::_getImportDirectory() . "/" . ilSession::get("tst_import_subdir") . "/" . $mob["uri"];
                } else {
                    $importfile = ilObjQuestionPool::_getImportDirectory() . "/" . ilSession::get("qpl_import_subdir") . "/" . $mob["uri"];
                }
                $media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, false);
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
                $this->importSuggestedSolution(
                    $this->object->getId(),
                    $suggested_solution["solution"]->getContent(),
                    $suggested_solution["gap_index"]
                );
            }
        }
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate();
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
        return $import_mapping;
    }
}
