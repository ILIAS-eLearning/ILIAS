<?php

/**
 * PDF Gen for Rubric
 */

class ilRubricPDF
{
    protected $db;
    protected $obj_id;

    public function __construct($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return mixed
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    public function exportGradedPDF()
    {
        $history_id = $_REQUEST['grader_history'];
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php");
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");
        $rubricObj = new ilLPRubricGrade($this->getObjId());
        $rubricGui = new ilLPRubricGradeGUI();
        if ($rubricObj->objHasRubric()) {
            $rubricGui->setRubricData($rubricObj->load());
            $rubricGui->setUserHistoryId($history_id);
            $rubricGui->setRubricData($rubricObj->load());
            $rubricGui->setUserHistory($rubricObj->getUserHistory((int)$_REQUEST['user_id']));
            $rubricGui->setUserData($rubricObj->getRubricUserGradeData((int)$_REQUEST['user_id'], $history_id));
            $rubricGui->setRubricComment($rubricObj->getRubricComment($_REQUEST['user_id'], $history_id));
            $html = $rubricGui->getPDFViewHTML($this->getObjId());
            $html = self::removeScriptElements($html);
            $history = $rubricGui->getUserHistory();
            $date = is_null($history[$history_id]['create_date']) ? date("Ymd") : $history[$history_id]['create_date'];
            $css = '<style>.ilHeaderDesc{display:block;text-align:center;}table{table-layout: fixed;}td{padding: 10px;border: 1px solid grey;}
					tr{padding: 10px;border: 1px solid grey;}th{padding: 10px;border: 1px solid grey;}</style>';
            self::generatePDF($css . $html, 'D', 'graded_rubric_' . $date);
        }
    }

    public function exportPDF()
    {
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php");
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");
        $rubricObj = new ilLPRubricGrade($this->getObjId());
        $rubricGui = new ilLPRubricGradeGUI();
        if ($rubricObj->objHasRubric()) {
            $rubricGui->setRubricData($rubricObj->load());
            $html = $rubricGui->getPDFViewHTML($this->getObjId());
            $html = self::removeScriptElements($html);
            $css = '<style>.ilHeaderDesc{display:block;text-align:center;}table{table-layout: fixed;}td{padding: 10px;border: 1px solid grey;}
					tr{padding: 10px;border: 1px solid grey;}th{padding: 10px;border: 1px solid grey;}</style>';
            self::generatePDF($css . $html, 'D', 'rubric');
        }
    }

    public static function generatePDF($pdf_output, $output_mode, $filename = null)
    {
        ob_clean();
        $pdf_output = preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $pdf_output);
        $pdf_output = preg_replace("/href=\"\\.\\//ims", "href=\"" . ILIAS_HTTP_PATH . "/", $pdf_output);

        $pdf_factory = new ilHtmlToPdfTransformerFactory();
        $pdf_factory->deliverPDFFromHTMLString(
            $pdf_output,
            'test.pdf',
            ilHtmlToPdfTransformerFactory::PDF_OUTPUT_DOWNLOAD,
            "Test",
            "ContentExport"
        );
    }

    /**
     * @param $html
     * @return string
     */
    private static function removeScriptElements($html)
    {
        if (!is_string($html) || !strlen(trim($html))) {
            return $html;
        }
        $dom = new DOMDocument("1.0", "utf-8");
        if (!@$dom->loadHTML('<?xml encoding="UTF-8">' . $html)) {
            return $html;
        }
        $invalid_elements = array();
        $script_elements     = $dom->getElementsByTagName('script');
        foreach ($script_elements as $elm) {
            $invalid_elements[] = $elm;
        }
        foreach ($invalid_elements as $elm) {
            $elm->parentNode->removeChild($elm);
        }
        $dom->encoding = 'UTF-8';
        $cleaned_html = $dom->saveHTML();
        if (!$cleaned_html) {
            return $html;
        }
        return $cleaned_html;
    }
}
