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

use ILIAS\TestQuestionPool\Questions\QuestionLMExportable;

/**
 * Scorm 2004 Question Exporter
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 *
 * @version $Id: class.ilQuestionExporter.php 12658 2006-11-29 08:51:48Z akill $
 *
 * @ingroup components\ILIASScormAicc
 */
class ilQuestionExporter
{
    /**
     * @var ilLanguage
     */
    protected ilLanguage $lng;

    public static array $exported = []; //json data for all exported questions (class variable)
    public static array $mobs = []; //json data for all mobs  (class variable)
    public static array $media_files = []; //json data for all files  (class variable)

    public ilDBInterface $db;			// database object
    public int $ref_id;		// reference ID
    public int $inst_id;		// installation id
    public ?assQuestionGUI $q_gui;			// Question GUI object
    public ilTemplate $tpl;			// question template
    public string $json;			// json object for current question
    public mixed $json_decoded;	// json object (decoded) for current question
    public bool $preview_mode;	// preview mode activated yes/no

    /**
     * Constructor
     * @access	public
     */
    public function __construct(bool $a_preview_mode = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $this->db = $ilDB;
        $this->lng = $lng;

        $this->lng->loadLanguageModule('assessment');

        $this->inst_id = IL_INST_ID;

        $this->preview_mode = $a_preview_mode;

        $this->tpl = new ilTemplate("tpl.question_export.html", true, true, "components/ILIAS/COPage");

        // fix for bug 5386, alex 29.10.2009
        if (!$a_preview_mode) {
            $this->tpl->setVariable("FORM_BEGIN", "<form onsubmit='return false;'>");
            $this->tpl->setVariable("FORM_END", "</form>");
        }
    }


    public function exportQuestion($a_ref_id, $a_image_path = null, $a_output_mode = "presentation")
    {
        if ($a_ref_id != "") {
            $inst_id = ilInternalLink::_extractInstOfTarget($a_ref_id);
            if (!($inst_id > 0)) {
                $q_id = ilInternalLink::_extractObjIdOfTarget($a_ref_id);
            }
        }

        $this->q_gui = assQuestionGUI::_getQuestionGUI("", $q_id);

        if (is_null($this->q_gui) || !is_object($this->q_gui->getObject())) {
            return "Error: Question not found.";
        }

        $type = $this->q_gui->getObject()->getQuestionType();
        if ($this->q_gui->getObject() instanceof QuestionLMExportable) {
            $this->q_gui->getObject()->setExportImagePath($a_image_path);
            $this->q_gui->getObject()->feedbackOBJ->setPageObjectOutputMode($a_output_mode);
            $this->json = $this->q_gui->getObject()->toJSON();
            $this->json_decoded = json_decode($this->json);
            self::$exported[$this->json_decoded->id] = $this->json;
            self::$mobs[$this->json_decoded->id] = $this->json_decoded->mobs;
            return $this->$type();
        } else {
            return "Error: Question Type not implemented/Question editing not finished";
        }
    }

    public static function questionsJS(?array $a_qids = null): string
    {
        $exportstring = '';
        if (!is_array($a_qids)) {
            $exportstring = 'var questions = new Array();';
        }
        foreach (self::$exported as $key => $value) {
            if (!is_array($a_qids) || in_array($key, $a_qids)) {
                $exportstring .= "questions[$key]= $value;";
            }
        }
        return $exportstring;
    }

    private function assSingleChoice(): string
    {
        $this->tpl->setCurrentBlock("singlechoice");
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        if ($this->json_decoded->path ?? false) {
            $this->tpl->setVariable(
                "HANDLE_IMAGES",
                "ilias.questions.handleMCImages(" . $this->json_decoded->id . ");"
            );
        }
        $this->tpl->parseCurrentBlock();
        foreach ($this->json_decoded->answers as $answer) {
            if ($answer->image != "") {
                array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . $answer->image);
                if (is_file($this->q_gui->getObject()->getImagePath() . "thumb." . $answer->image)) {
                    array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . "thumb." . $answer->image);
                }
            }
        }
        return $this->tpl->get();
    }

    private function assMultipleChoice(): string
    {
        $this->tpl->setCurrentBlock("multiplechoice");
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        if ($this->json_decoded->selection_limit) {
            $this->tpl->setVariable('SELECTION_LIMIT_HINT', sprintf(
                $this->lng->txt('ass_mc_sel_lim_hint'),
                $this->json_decoded->selection_limit,
                count($this->json_decoded->answers)
            ));

            $this->tpl->setVariable('SELECTION_LIMIT_VALUE', $this->json_decoded->selection_limit);
        } else {
            $this->tpl->setVariable('SELECTION_LIMIT_VALUE', 'null');
        }
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        if (isset($this->json_decoded->path)) {
            $this->tpl->setVariable(
                "HANDLE_IMAGES",
                "ilias.questions.handleMCImages(" . $this->json_decoded->id . ");"
            );
        }
        $this->tpl->parseCurrentBlock();
        foreach ($this->json_decoded->answers as $answer) {
            if ($answer->image != "") {
                array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . $answer->image);
                array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . "thumb." . $answer->image);
            }
        }
        return $this->tpl->get();
    }


    private function assKprimChoice(): string
    {
        $this->tpl->setCurrentBlock("kprimchoice");

        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);

        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }

        if ($this->json_decoded->path ?? false) {
            $this->tpl->setVariable(
                "HANDLE_IMAGES",
                "ilias.questions.handleKprimImages(" . $this->json_decoded->id . ");"
            );
        }

        $this->tpl->setVariable('OPTION_LABEL_TRUE', $this->json_decoded->trueOptionLabel);
        $this->tpl->setVariable('OPTION_LABEL_FALSE', $this->json_decoded->falseOptionLabel);

        $this->tpl->setVariable('VALUE_TRUE', 1);
        $this->tpl->setVariable('VALUE_FALSE', 0);

        $this->tpl->parseCurrentBlock();

        foreach ($this->json_decoded->answers as $answer) {
            if (is_object($answer->image)) {
                self::$media_files[] = $answer->getImageFsPath();
                self::$media_files[] = $answer->getThumbFsPath();
            } elseif (is_string($answer->image)) {
                self::$media_files[] = $this->q_gui->getObject()->getImagePath() . $answer->image;
                if (is_file($this->q_gui->getObject()->getImagePath() . "thumb." . $answer->image)) {
                    self::$media_files[] = $this->q_gui->getObject()->getImagePath() . "thumb." . $answer->image;
                }
            }
        }

        return $this->tpl->get();
    }

    private function assTextQuestion(): string
    {
        $maxlength = $this->json_decoded->maxlength == 0 ? 4096 : $this->json_decoded->maxlength;
        $this->tpl->setCurrentBlock("textquestion");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        $this->tpl->setVariable("VAL_MAXLENGTH", $maxlength);
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assClozeTest(): string
    {
        $this->tpl->setCurrentBlock("clozequestion");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assLongMenu(): string
    {
        $this->tpl->setCurrentBlock("longmenu");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assOrderingQuestion(): string
    {
        $this->tpl->setCurrentBlock("orderingquestion");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        if ($this->q_gui->getObject()->getOrderingType() == assOrderingQuestion::OQ_PICTURES) {
            $this->tpl->setVariable("VAL_SUBTYPE", "_images");
            $this->tpl->setVariable(
                "HANDLE_IMAGES",
                "ilias.questions.handleOrderingImages(" . $this->json_decoded->id . ");"
            );

            foreach ($this->json_decoded->answers as $answer) {
                if ($answer->answertext != "") {
                    array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . $answer->answertext);
                    array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . "thumb." . $answer->answertext);
                }
            }
        } else {
            $this->tpl->setVariable("VAL_SUBTYPE", "_terms");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assMatchingQuestion(): string
    {
        $this->tpl->setCurrentBlock("matchingquestion");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("BTN_LABEL_RESET", $this->lng->txt("reset_terms"));
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assImagemapQuestion(): string
    {
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        array_push(self::$media_files, $this->q_gui->getObject()->getImagePath() . $this->q_gui->getObject()->getImageFilename());
        $this->tpl->setCurrentBlock("mapareas");
        $areas = $this->json_decoded->answers;
        //set areas in PHP cause of inteference between pure and highlighter
        foreach ($areas as $area) {
            $this->tpl->setVariable("VAL_TOOLTIP", htmlspecialchars($area->answertext));
            $this->tpl->setVariable("VAL_COORDS", $area->coords);
            $this->tpl->setVariable("VAL_ORDER", $area->order);
            $this->tpl->setVariable("VAL_AREA", $area->area);
            $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setCurrentBlock("imagemapquestion");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assTextSubset(): string
    {
        $this->tpl->setCurrentBlock("textsubset");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assOrderingHorizontal(): string
    {
        $this->tpl->setCurrentBlock("orderinghorizontal");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }

    private function assErrorText(): string
    {
        $this->tpl->setCurrentBlock("errortext");
        $this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
        $this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
        if ($this->preview_mode) {
            $this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
        }
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }
}
