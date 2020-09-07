<?php
require_once 'Modules/TestQuestionPool/classes/export/qti12/class.assQuestionExport.php';
class assLongMenuExportQti21 extends assQuestionExport
{
    /**
     * @var assLongMenu
     */
    public $object;

    public function toXML($a_include_header = true, $a_include_binary = true, $a_shuffle = false, $test_output = false, $force_image_references = false)
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        include_once("./Services/Xml/classes/class.ilXmlWriter.php");
        $xml = new ilXmlWriter;
        // set xml header
        $xml->xmlHeader();
        $attrs = array(
            'xmlns' => "http://www.imsglobal.org/xsd/imsqti_v2p1",
            'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation' => 'http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/qti/qtiv2p1/imsqti_v2p1p1.xsd http://www.w3.org/1998/Math/MathML http://www.w3.org/Math/XMLSchema/mathml2/mathml2.xsd',
            "identifier" => "il_" . IL_INST_ID . "_qst_" . $this->object->getId(),
            "title" => $this->object->getTitle(),
            //"maxattempts" => $this->object->getNrOfTries(),
            'adaptive' => 'false',
            'timeDependent' => 'false'
        );
        $xml->xmlStartTag("assessmentItem", $attrs, false, true, false);
        // add question description
        $correct_answers = $this->object->getCorrectAnswers();
        $answers = $this->object->getAnswers();
        $a = 0;
        $inlineChoice = array();
        foreach ($answers as $key => $values) {
            $real_id = $key + 1;
            $inlineChoiceString = '<inlineChoiceInteraction responseIdentifier="LONGMENU_' . $real_id . '" shuffle="false" required="false">';
            $attrs = array(
                "identifier" => "LONGMENU_" . $real_id,
                "cardinality" => "single",
                "baseType" => "identifier"
            );
            $xml->xmlStartTag("responseDeclaration", $attrs);
            $xml->xmlStartTag("correctResponse");
            $xml->xmlElement("value", null, $correct_answers[$key][0][0]);
            $xml->xmlEndTag("correctResponse");
            $attrs = array(
                "defaultValue" => "0",
                "lowerBound" => "0",
                "upperBound" => $correct_answers[$key][1]
            );
            $xml->xmlStartTag("mapping", $attrs);

            foreach ($values as $index => $value) {
                $points = 0;
                if (in_array($value, $correct_answers[$key][0])) {
                    $points = $correct_answers[$key][1];
                }
                $attrs = array(
                    "mapKey" => $value,
                    "mappedValue" => $points
                );
                $inlineChoiceString .= '<inlineChoice identifier="' . $value . '" fixed="false" showHide="show">' . $value . '</inlineChoice>';
                $xml->xmlElement("mapEntry", $attrs);
            }
            $xml->xmlEndTag("mapping");
            $xml->xmlEndTag("responseDeclaration");
            $inlineChoiceString .= '</inlineChoiceInteraction>';
            $inlineChoice[$real_id] = $inlineChoiceString;
        }
        $attrs = array(
            "identifier" => "SCORE",
            "cardinality" => "single",
            "baseType" => "float"
        );
        $xml->xmlStartTag("outcomeDeclaration", $attrs);
        $xml->xmlStartTag("defaultValue");
        $xml->xmlElement("value", null, 0);
        $xml->xmlEndTag("defaultValue");
        $xml->xmlEndTag("outcomeDeclaration");

        $attrs = array(
            "identifier" => "MAXSCORE",
            "cardinality" => "single",
            "baseType" => "float"
        );
        $xml->xmlStartTag("outcomeDeclaration", $attrs);
        $xml->xmlStartTag("defaultValue");
        $xml->xmlElement("value", null, $this->object->getMaximumPoints());
        $xml->xmlEndTag("defaultValue");
        $xml->xmlEndTag("outcomeDeclaration");

        $attrs = array(
            "identifier" => "FEEDBACKBASIC",
            "cardinality" => "single",
            "baseType" => "identifier",
            "view" => "testConstructor"
        );
        
        $xml->xmlStartTag("outcomeDeclaration", $attrs);
        $xml->xmlStartTag("defaultValue");
        $xml->xmlElement("value", null, "TODO IMPLEMENT FEEDBACK");
        $xml->xmlEndTag("defaultValue");
        $xml->xmlEndTag("outcomeDeclaration");

        $longmenu_text = $this->object->getLongMenuTextValue();
    
        for ($i = 1; $i <= sizeof($answers); $i++) {
            $longmenu_text = preg_replace("/\\[" . assLongMenu::GAP_PLACEHOLDER . " " . $i . "]/", $inlineChoice[$i], $longmenu_text);
        }
        $longmenu_text = $this->object->getQuestion() . $longmenu_text;
        $xml->xmlStartTag("itemBody", $attrs);
        $xml->xmlElement("div", null, $longmenu_text, true, false);
        $xml->xmlEndTag("itemBody");

        $xml->xmlStartTag("responseProcessing");
        foreach ($answers as $key => $values) {
            $xml->xmlStartTag("responseCondition");
            $xml->xmlStartTag("responseIf");
            $xml->xmlStartTag("not");
            $xml->xmlStartTag("isNull");
            $xml->xmlElement("variable", array("identifier" => 'LONGMENU_' . ($key + 1) ));
            $xml->xmlEndTag("isNull");
            $xml->xmlEndTag("not");
            $xml->xmlStartTag("setOutcomeValue", array("identifier" => 'SCORE'));
            $xml->xmlStartTag("sum");
            $xml->xmlElement("variable", array("identifier" => 'SCORE'));
            $xml->xmlElement("mapResponse", array("identifier" => 'LONGMENU_' . ($key + 1) ));
            $xml->xmlEndTag("sum");
            $xml->xmlEndTag("setOutcomeValue");
            $xml->xmlStartTag("setOutcomeValue", array("identifier" => 'FEEDBACKBASIC'));
            $xml->xmlElement("value", array('baseType' => 'identifier'), "incorrect");
            $xml->xmlEndTag("setOutcomeValue");
            $xml->xmlEndTag("responseIf");
            $xml->xmlEndTag("responseCondition");
        }
        $xml->xmlEndTag("responseProcessing");
        $xml->xmlEndTag("assessmentItem");
        $xml = $xml->xmlDumpMem(false);
        if (!$a_include_header) {
            $pos = strpos($xml, "?>");
            $xml = substr($xml, $pos + 2);
        }
        return $xml;
    }
}
