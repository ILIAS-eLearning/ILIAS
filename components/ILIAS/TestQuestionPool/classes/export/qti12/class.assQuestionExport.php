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

use ILIAS\TA\Questions\assQuestionSuggestedSolution;
use ILIAS\Test\QuestionIdentifiers;

/**
* Class for question exports
*
* exportQuestion is a basis class question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup components\ILIASTestQuestionPool
*/
class assQuestionExport
{
    /**
     * @var assQuestion
     */
    public $object;

    /**
    * assQuestionExport constructor
    *
    * @param object $a_object The question object
    * @access public
    */
    public function __construct($a_object)
    {
        $this->object = $a_object;
    }

    /**
     * @param ilXmlWriter $a_xml_writer
     */
    protected function addAnswerSpecificFeedback(ilXmlWriter $a_xml_writer, $answers): void
    {
        foreach ($answers as $index => $answer) {
            $linkrefid = "response_$index";
            $attrs = array(
                "ident" => $linkrefid,
                "view" => "All"
            );
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackExportPresentation(
                $this->object->getId(),
                0,
                $index
            );
            $this->addQTIMaterial($a_xml_writer, $fb);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }
    }

    /**
     * @param ilXmlWriter $a_xml_writer
     */
    protected function addGenericFeedback(ilXmlWriter $a_xml_writer): void
    {
        $this->exportFeedbackOnly($a_xml_writer);
    }

    public function exportFeedbackOnly($a_xml_writer): void
    {
        $feedback_allcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            true
        );
        $feedback_onenotcorrect = $this->object->feedbackOBJ->getGenericFeedbackExportPresentation(
            $this->object->getId(),
            false
        );
        if (strlen($feedback_allcorrect . $feedback_onenotcorrect)) {
            $a_xml_writer->xmlStartTag("resprocessing");
            $a_xml_writer->xmlStartTag("outcomes");
            $a_xml_writer->xmlStartTag("decvar");
            $a_xml_writer->xmlEndTag("decvar");
            $a_xml_writer->xmlEndTag("outcomes");

            if (strlen($feedback_allcorrect)) {
                $attrs = array(
                    "continue" => "Yes"
                );
                $a_xml_writer->xmlStartTag("respcondition", $attrs);
                // qti conditionvar
                $a_xml_writer->xmlStartTag("conditionvar");
                $attrs = array(
                    "respident" => "points"
                );
                $a_xml_writer->xmlElement("varequal", $attrs, $this->object->getPoints());
                $a_xml_writer->xmlEndTag("conditionvar");
                // qti displayfeedback
                $attrs = array(
                    "feedbacktype" => "Response",
                    "linkrefid" => "response_allcorrect"
                );
                $a_xml_writer->xmlElement("displayfeedback", $attrs);
                $a_xml_writer->xmlEndTag("respcondition");
            }

            if (strlen($feedback_onenotcorrect)) {
                $attrs = array(
                    "continue" => "Yes"
                );
                $a_xml_writer->xmlStartTag("respcondition", $attrs);
                // qti conditionvar
                $a_xml_writer->xmlStartTag("conditionvar");
                $a_xml_writer->xmlStartTag("not");
                $attrs = array(
                    "respident" => "points"
                );
                $a_xml_writer->xmlElement("varequal", $attrs, $this->object->getPoints());
                $a_xml_writer->xmlEndTag("not");
                $a_xml_writer->xmlEndTag("conditionvar");
                // qti displayfeedback
                $attrs = array(
                    "feedbacktype" => "Response",
                    "linkrefid" => "response_onenotcorrect"
                );
                $a_xml_writer->xmlElement("displayfeedback", $attrs);
                $a_xml_writer->xmlEndTag("respcondition");
            }
            $a_xml_writer->xmlEndTag("resprocessing");
        }

        if (strlen($feedback_allcorrect)) {
            $attrs = array(
                "ident" => "response_allcorrect",
                "view" => "All"
            );
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $this->addQTIMaterial($a_xml_writer, $feedback_allcorrect);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }
        if (strlen($feedback_onenotcorrect)) {
            $attrs = array(
                "ident" => "response_onenotcorrect",
                "view" => "All"
            );
            $a_xml_writer->xmlStartTag("itemfeedback", $attrs);
            // qti flow_mat
            $a_xml_writer->xmlStartTag("flow_mat");
            $this->addQTIMaterial($a_xml_writer, $feedback_onenotcorrect);
            $a_xml_writer->xmlEndTag("flow_mat");
            $a_xml_writer->xmlEndTag("itemfeedback");
        }
    }

    /**
    * Returns a QTI xml representation of the question
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        return '';
    }

    /**
     * adds a qti meta data field with given name and value to the passed xml writer
     * (xml writer must be in context of opened "qtimetadata" tag)
     *
     * @final
     * @access protected
     * @param ilXmlWriter $a_xml_writer
     * @param string $fieldLabel
     * @param string $fieldValue
     */
    final protected function addQtiMetaDataField(ilXmlWriter $a_xml_writer, $fieldLabel, $fieldValue): void
    {
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, $fieldLabel);
        $a_xml_writer->xmlElement("fieldentry", null, $fieldValue);
        $a_xml_writer->xmlEndTag("qtimetadatafield");
    }

    /**
     * adds a qti meta data field for ilias specific information of "additional content editing mode"
     * (xml writer must be in context of opened "qtimetadata" tag)
     *
     * @final
     * @access protected
     * @param ilXmlWriter $a_xml_writer
     */
    final protected function addAdditionalContentEditingModeInformation(ilXmlWriter $a_xml_writer): void
    {
        $this->addQtiMetaDataField(
            $a_xml_writer,
            'additional_cont_edit_mode',
            $this->object->getAdditionalContentEditingMode()
        );
    }

    /**
     * @param ilXmlWriter $xmlwriter
     */
    protected function addGeneralMetadata(ilXmlWriter $xmlwriter): void
    {
        $this->addQtiMetaDataField($xmlwriter, 'externalId', $this->object->getExternalId());

        $this->addQtiMetaDataField(
            $xmlwriter,
            'ilias_lifecycle',
            $this->object->getLifecycle()->getIdentifier()
        );

        $this->addQtiMetaDataField(
            $xmlwriter,
            'lifecycle',
            $this->object->getLifecycle()->getMappedLomLifecycle()
        );
    }

    public const ITEM_SOLUTIONHINT = 'solutionhint';

    protected function addSolutionHints(ilXmlWriter $writer): ilXmlWriter
    {
        $question_id = (int) $this->object->getId();
        $list = ilAssQuestionHintList::getListByQuestionId($question_id);

        foreach ($list as $hint) {
            $attrs = [
                'index' => $hint->getIndex(),
                'points' => $hint->getPoints()
            ];
            $data = $hint->getText();
            $writer->xmlElement(self::ITEM_SOLUTIONHINT, $attrs, $data);
        }
        return $writer;
    }

    protected function addSuggestedSolutionLink(ilXmlWriter $writer, assQuestionSuggestedSolution $suggested_solution): ilXmlWriter
    {
        if (!$suggested_solution->isOfTypeLink()) {
            return $writer;
        }

        if (preg_match("/il_(\d*?)_(\w+)_(\d+)/", $suggested_solution->getInternalLink(), $matches) !== 1) {
            return $writer;
        }

        $writer->xmlStartTag("material");
        $intlink = "il_" . IL_INST_ID . "_" . $matches[2] . "_" . $matches[3];
        if (strcmp($matches[1], "") != 0) {
            $intlink = $suggested_solution->getInternalLink();
        }
        $attrs = array(
            "label" => "suggested_solution"
        );
        $writer->xmlElement("mattext", $attrs, $intlink);
        $writer->xmlEndTag("material");

        return $writer;
    }

    public function addQTIMaterial(ilXmlWriter $a_xml_writer, string $a_material, bool $close_material_tag = true, bool $add_mobs = true): void
    {
        $a_xml_writer->xmlStartTag("material");
        $attrs = array(
            "texttype" => "text/plain"
        );
        if (ilUtil::isHTML($a_material)) {
            $attrs["texttype"] = "text/xhtml";
        }
        $a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));
        if ($add_mobs) {
            $mobs = ilObjMediaObject::_getMobsOfObject("qpl:html", $this->object->getId());
            foreach ($mobs as $mob) {
                $moblabel = "il_" . IL_INST_ID . "_mob_" . $mob;
                if (str_contains($a_material, "mm_$mob")) {
                    if (ilObjMediaObject::_exists($mob)) {
                        $mob_obj = new ilObjMediaObject($mob);
                        $imgattrs = array(
                            "label" => $moblabel,
                            "uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
                        );
                    }
                    $a_xml_writer->xmlElement("matimage", $imgattrs, null);
                }
            }
        }
        if ($close_material_tag) {
            $a_xml_writer->xmlEndTag("material");
        }
    }
}
