<?php declare(strict_types=1);

/**
 * Class ilForumLP
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumLP extends ilObjectLP
{
    public function appendModeConfiguration(int $mode, ilRadioOption $modeElement) : void
    {
        global $DIC;

        if (ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION === $mode) {
            $num_postings = new ilNumberInputGUI($DIC->language()->txt('frm_lp_number_of_postings'), 'number_of_postings');
            $num_postings->allowDecimals(false);
            $num_postings->setSize(3);
            $num_postings->setRequired(true);
            $num_postings->setValue(5); // TODO: READ FROM forum object for $this->obj_id
            $modeElement->addSubItem($num_postings);
        }
    }

    public function saveModeConfiguration(ilPropertyFormGUI $form, bool &$modeChanged) : void
    {
        // TODO: Store $form->getInput('number_of_postings'); for $this->obj_id
    }
  
    public static function getDefaultModes($a_lp_active)
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
        ];
    }

    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes()
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
        ];
    }
}
