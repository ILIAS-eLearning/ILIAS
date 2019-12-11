<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Fields;

use ilTemplate;
use ilTextInputGUI;

/**
 * Class ImageFormPopup
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ImageFormPopup extends ilTextInputGUI {
    /**
     * @param string $a_mode
     *
     * @return string
     * @throws \ilTemplateException
     */
    public function render($a_mode = '') {
        global $DIC;
        
        $tpl = new ilTemplate("tpl.ImageMapEditorFormPopUp.html", true, true, "Services/AssessmentQuestion");
        $tpl->setVariable('POPUP_TITLE', $DIC->language()->txt('asq_imagemap_popup_title'));
        $tpl->setVariable('IMAGE_SRC', $this->getValue());
        $tpl->setVariable('OK', $DIC->language()->txt('ok'));
        $tpl->setVariable('CANCEL', $DIC->language()->txt('cancel'));
        return $tpl->get();
    }
    
    public function setValueByArray($values) {
        //do nothing as it has no post value and setvaluebypost resets value
    }
}