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
* @ingroup components\ILIASTestQuestionPool
*/
class assFormulaQuestionImport extends assQuestionImport
{
    public function fromXML(
        string $importdirectory,
        int $user_id,
        ilQTIItem $item,
        int $questionpool_id,
        ?int $tst_id,
        ?ilObject &$tst_object,
        int &$question_counter,
        array $import_mapping
    ): array {
        // empty session variable for imported xhtml mobs
        ilSession::clear('import_mob_xhtml');

        $this->object->setTitle($item->getTitle());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($user_id);
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
                $importfile = $importdirectory . DIRECTORY_SEPARATOR . $mob["uri"];
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
            $import_mapping[$item->getIdent()] = ["pool" => $q_1_id, "test" => $question_id];
        } else {
            $import_mapping[$item->getIdent()] = ["pool" => $this->object->getId(), "test" => 0];
        }
        return $import_mapping;
    }
}
