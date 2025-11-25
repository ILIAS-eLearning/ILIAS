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

        $feedbacksgeneric = [];

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

        $this->importUnitsAndUnitCategories($item);

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
        $this->importSuggestedSolutions($this->object->getId(), $item->suggested_solutions);
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate();
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = ["pool" => $q_1_id, "test" => $question_id];
        } else {
            $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        }
        return $import_mapping;
    }

    private function importUnitsAndUnitCategories(ilQTIItem $item): void
    {
        /** @var ilUnitConfigurationRepository $unit_repository */
        $unit_repository = $this->object->getUnitrepository();
        foreach ($item->getUnitCategoryObjets() as $unit_category) {
            $old_category_id = $unit_category->getId();

            $unit_category->setQuestionFi($this->object->getId());
            $unit_repository->saveNewUnitCategory($unit_category);

            $units = [];
            $base_unit_map = [];

            foreach ($item->getUnitObjects() as $unit) {
                if ($unit->getCategory() !== $old_category_id) {
                    continue;
                }

                $old_unit_id = $unit->getId();
                $old_base_unit_id = $unit->getBaseUnit();
                $old_unit_factor = $unit->getFactor();
                $old_sequence = $unit->getSequence();

                $unit->setCategory($unit_category->getId());
                $unit->setFactor($old_unit_factor);
                $unit->setSequence($old_sequence);

                $unit_repository->createNewUnit($unit);
                $unit->setBaseUnit($old_base_unit_id);

                $units[] = $unit;
                $base_unit_map[$old_unit_id] = $unit->getId();

                $this->mapAssignedVariableUnits($unit, $old_unit_id);
                $this->mapAssignedResultUnits($unit, $old_unit_id);
            }

            foreach ($units as $unit) {
                $unit->setBaseUnit($base_unit_map[$unit->getBaseUnit()] ?? 0);
                $unit_repository->saveUnit($unit);
            }
        }
    }

    private function mapAssignedVariableUnits(assFormulaQuestionUnit $unit, int $old_unit_id): void
    {
        /** @var assFormulaQuestionVariable $variable */
        foreach ($this->object->getVariables() as $variable) {
            $variable_unit = $variable->getUnit();
            if ($variable_unit instanceof assFormulaQuestionUnit && $variable_unit->getId() === $old_unit_id) {
                $variable_unit->setId($unit->getId());
            }
        }
    }

    private function mapAssignedResultUnits(assFormulaQuestionUnit $unit, int $old_unit_id): void
    {
        /** @var assFormulaQuestionResult $result */
        foreach ($this->object->getResults() as $result) {
            $result_unit = $result->getUnit();
            if ($result_unit instanceof assFormulaQuestionUnit && $result_unit->getId() === $old_unit_id) {
                $result_unit->setId($unit->getId());
            }
        }

        /** @var assFormulaQuestionUnit[] $result */
        foreach ($this->object->getAllResultUnits() as $result) {
            foreach ($result as $result_unit) {
                if ($result_unit instanceof assFormulaQuestionUnit && $result_unit->getId() === $old_unit_id) {
                    $result_unit->setId($unit->getId());
                }
            }
        }
    }
}
