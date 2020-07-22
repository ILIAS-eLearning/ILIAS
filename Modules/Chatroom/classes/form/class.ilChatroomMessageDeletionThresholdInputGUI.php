<?php

/**
 * Class ilChatroomMessageDeletionThresholdInputGUI
 */
class ilChatroomMessageDeletionThresholdInputGUI extends ilNumberInputGUI
{
    /**
     * @var ilSelectInputGUI
     */
    protected $thresholdUnits;

    /**
     * ilChatroomMessageDeletionThresholdInputGUI constructor.
     * @param string           $a_title
     * @param string           $a_postvar
     * @param ilSelectInputGUI $thresholdUnits
     */
    public function __construct($a_title = "", $a_postvar = "", ilSelectInputGUI $thresholdUnits)
    {
        parent::__construct($a_title, $a_postvar);
        $this->thresholdUnits = $thresholdUnits;
    }

    /**
     * @inheritdoc
     */
    public function checkInput()
    {
        $isValid = parent::checkInput();

        if (!$isValid) {
            return false;
        }

        $this->setValueByArray((array) $_POST);
        $this->thresholdUnits->setValueByArray((array) $_POST);

        $unit = $this->thresholdUnits->getValue();
        switch (true) {
            case $unit == 'days' && $this->getValue() > 31:
                $this->setAlert(sprintf(
                    $GLOBALS['DIC']->language()->txt('chat_deletion_ival_max_val'),
                    $GLOBALS['DIC']->language()->txt('days'),
                    31
                ));
                return false;
                break;

            case $unit == 'weeks' && $this->getValue() > 52:
                $this->setAlert(sprintf(
                    $GLOBALS['DIC']->language()->txt('chat_deletion_ival_max_val'),
                    $GLOBALS['DIC']->language()->txt('weeks'),
                    52
                ));
                return false;
                break;

            case $unit == 'months' && $this->getValue() > 12:
                $this->setAlert(sprintf(
                    $GLOBALS['DIC']->language()->txt('chat_deletion_ival_max_val'),
                    $GLOBALS['DIC']->language()->txt('months'),
                    12
                ));
                return false;
                break;
        }

        return true;
    }
}
