<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilFileInputGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.assAnswerImagemap.php';

/**
 * Class ilHtmlImageMapFileInputGUI
 */
class ilHtmlImageMapFileInputGUI extends ilFileInputGUI
{
    /**
     * @var ASS_AnswerImagemap[]
     */
    protected $shapes = array();
    
    /**
     * {@inheritdoc}
     */
    public function checkInput()
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC['lng'];

        if (!parent::checkInput()) {
            return false;
        }

        $tmp_file_name = $_FILES[$this->getPostVar()]['tmp_name'];
        if (strlen($tmp_file_name) == 0) {
            return true;
        }

        if (!is_readable($tmp_file_name)) {
            $this->setAlert($lng->txt('ass_imap_map_file_not_readable'));
            return false;
        }

        $contents = file_get_contents($tmp_file_name);
        $matches = null;
        if (
            !preg_match_all('/<area(.+)>/siU', $contents, $matches) ||
            !is_array($matches) ||
            !isset($matches[1]) ||
            count($matches[1]) == 0
        ) {
            $this->setAlert($lng->txt('ass_imap_no_map_found'));
            return false;
        }

        for ($i = 0; $i < count($matches[1]); $i++) {
            preg_match("/alt\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $alt);
            preg_match("/coords\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $coords);
            preg_match("/shape\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $shape);

            $this->shapes[] = new ASS_AnswerImagemap($alt[1], 0.0, $i, $coords[1], $shape[1]);
        }

        return true;
    }

    /**
     * @return ASS_AnswerImagemap[]
     */
    public function getShapes()
    {
        return $this->shapes;
    }
}
