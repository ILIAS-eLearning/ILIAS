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
* Class for formula question question exports
*
* assSingleChoiceExport is a class for single choice question exports
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id: class.assFormulaQuestionExport.php 1185 2010-02-02 08:36:26Z hschottm $
* @ingroup components\ILIASTestQuestionPool
*/
class assFormulaQuestionExport extends assQuestionExport
{
    /**
    * Returns a QTI xml representation of the question
    * Returns a QTI xml representation of the question and sets the internal
    * domxml variable with the DOM XML representation of the QTI xml representation
    */
    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false): string
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $a_xml_writer = new ilXmlWriter();
        // set xml header
        $a_xml_writer->xmlHeader();
        $a_xml_writer->xmlStartTag("questestinterop");
        $attrs = [
            "ident" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle()
        ];
        $a_xml_writer->xmlStartTag("item", $attrs);
        // add question description
        $a_xml_writer->xmlElement("qticomment", null, $this->object->getComment());
        $a_xml_writer->xmlStartTag("itemmetadata");
        $a_xml_writer->xmlStartTag("qtimetadata");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "ILIAS_VERSION");
        $a_xml_writer->xmlElement("fieldentry", null, $ilias->getSetting("ilias_version"));
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "QUESTIONTYPE");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getQuestionType());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "AUTHOR");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getAuthor());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        $a_xml_writer->xmlStartTag("qtimetadatafield");
        $a_xml_writer->xmlElement("fieldlabel", null, "points");
        $a_xml_writer->xmlElement("fieldentry", null, $this->object->getPoints());
        $a_xml_writer->xmlEndTag("qtimetadatafield");
        foreach ($this->object->getVariables() as $variable) {
            $var = [
                "precision" => $variable->getPrecision(),
                "intprecision" => $variable->getIntprecision(),
                "rangemin" => $variable->getRangeMin(),
                "rangemax" => $variable->getRangeMax(),
                "unit" => (is_object($variable->getUnit())) ? $variable->getUnit()->getUnit() : "",
                "unitvalue" => (is_object($variable->getUnit())) ? $variable->getUnit()->getId() : ""
            ];
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $variable->getVariable());
            $a_xml_writer->xmlElement("fieldentry", null, serialize($var));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }
        foreach ($this->object->getResults() as $result) {
            $resultunits = $this->object->getResultUnits($result);
            $ru = [];
            foreach ($resultunits as $unit) {
                array_push($ru, ["unit" => $unit->getUnit(), "unitvalue" => $unit->getId()]);
            }
            $res = [
                "precision" => $result->getPrecision(),
                "tolerance" => $result->getTolerance(),
                "rangemin" => $result->getRangeMin(),
                "rangemax" => $result->getRangeMax(),
                "points" => $result->getPoints(),
                "formula" => $result->getFormula(),
                "rating" => ($result->getRatingSimple()) ? "" : ["sign" => $result->getRatingSign(), "value" => $result->getRatingValue(), "unit" => $result->getRatingUnit()],
                "unit" => (is_object($result->getUnit())) ? $result->getUnit()->getUnit() : "",
                "unitvalue" => (is_object($result->getUnit())) ? $result->getUnit()->getId() : "",
                "resultunits" => $ru,
                "resulttype" => $result->getResultType()
            ];
            $a_xml_writer->xmlStartTag("qtimetadatafield");
            $a_xml_writer->xmlElement("fieldlabel", null, $result->getResult());
            $a_xml_writer->xmlElement("fieldentry", null, serialize($res));
            $a_xml_writer->xmlEndTag("qtimetadatafield");
        }

        // additional content editing information
        $this->addAdditionalContentEditingModeInformation($a_xml_writer);
        $this->addGeneralMetadata($a_xml_writer);

        $a_xml_writer->xmlEndTag("qtimetadata");
        $a_xml_writer->xmlEndTag("itemmetadata");

        // PART I: qti presentation
        $attrs = [
            "label" => $this->object->getTitle()
        ];
        $a_xml_writer->xmlStartTag("presentation", $attrs);
        // add flow to presentation
        $a_xml_writer->xmlStartTag("flow");
        // add material with question text to presentation
        $this->addQTIMaterial($a_xml_writer, $this->object->getQuestion());
        // add answers to presentation
        $a_xml_writer->xmlEndTag("flow");
        $a_xml_writer->xmlEndTag("presentation");

        $this->addGenericFeedback($a_xml_writer);

        $a_xml_writer = $this->addSolutionHints($a_xml_writer);

        $a_xml_writer->xmlEndTag("item");
        $a_xml_writer->xmlEndTag("questestinterop");

        $xml = $a_xml_writer->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($xml, "?>");
            $xml = substr($xml, $pos + 2);
        }
        return $xml;
    }
}
