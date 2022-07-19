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
* Class for file upload question imports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assFileUploadImport extends assQuestionImport
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
    public function fromXML(&$item, $questionpool_id, &$tst_id, &$tst_object, &$question_counter, &$import_mapping) : void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        // empty session variable for imported xhtml mobs
        ilSession::clear('import_mob_xhtml');

        $presentation = $item->getPresentation();
        $duration = $item->getDuration();
        $now = getdate();
        $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);

        $feedbacksgeneric = array();

        $this->addGeneralMetadata($item);
        $this->object->setTitle($item->getTitle());
        $this->object->setNrOfTries($item->getMaxattempts());
        $this->object->setComment($item->getComment());
        $this->object->setAuthor($item->getAuthor());
        $this->object->setOwner($ilUser->getId());
        $this->object->setQuestion($this->object->QTIMaterialToString($item->getQuestiontext()));
        $this->object->setObjId($questionpool_id);
        $this->object->setEstimatedWorkingTime($duration["h"], $duration["m"], $duration["s"]);
        $this->object->setPoints($item->getMetadataEntry("points"));
        $this->object->setMaxSize($item->getMetadataEntry("maxsize") ? (int) $item->getMetadataEntry("maxsize") : null);
        $this->object->setAllowedExtensions($item->getMetadataEntry("allowedextensions"));
        // additional content editing mode information
        $this->object->setAdditionalContentEditingMode(
            $this->fetchAdditionalContentEditingModeInformation($item)
        );
        $this->object->saveToDb();

        $feedbacksgeneric = $this->getFeedbackGeneric($item);
        
        // handle the import of media objects in XHTML code
        $questiontext = $this->object->getQuestion();
        if (is_array(ilSession::get("import_mob_xhtml"))) {
            include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
            include_once "./Services/RTE/classes/class.ilRTE.php";
            foreach (ilSession::get("import_mob_xhtml") as $mob) {
                if ($tst_id > 0) {
                    $importfile = $this->getTstImportArchivDirectory() . '/' . $mob["uri"];
                } else {
                    $importfile = $this->getQplImportArchivDirectory() . '/' . $mob["uri"];
                }
                
                global $DIC; /* @var ILIAS\DI\Container $DIC */
                $DIC['ilLog']->write(__METHOD__ . ': import mob from dir: ' . $importfile);
                
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
        $this->object->saveToDb();
        if (count($item->suggested_solutions)) {
            foreach ($item->suggested_solutions as $suggested_solution) {
                $this->object->setSuggestedSolution($suggested_solution["solution"]->getContent(), $suggested_solution["gap_index"], true);
            }
            $this->object->saveToDb();
        }
        if ($tst_id > 0) {
            $q_1_id = $this->object->getId();
            $question_id = $this->object->duplicate(true, null, null, null, $tst_id);
            $tst_object->questions[$question_counter++] = $question_id;
            $import_mapping[$item->getIdent()] = array("pool" => $q_1_id, "test" => $question_id);
        } else {
            $import_mapping[$item->getIdent()] = array("pool" => $this->object->getId(), "test" => 0);
        }
    }
}
