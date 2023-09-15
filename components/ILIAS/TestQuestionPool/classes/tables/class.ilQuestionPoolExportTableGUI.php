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
 * Class ilQuestionPoolExportTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesTest
 */
class ilQuestionPoolExportTableGUI extends ilExportTableGUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);

        // NOT REQUIRED ANYMORE, PROBLEM NOW FIXED IN THE ROOT
        // KEEP CODE, JF OPINIONS / ROOT FIXINGS CAN CHANGE
        //$this->addCustomColumn($this->lng->txt('actions'), $this, 'formatActionsList');
    }

    /**
     * @param string $type
     * @param string $filename
     */
    protected function formatActionsList($type, $filename): string
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($this->lng->txt('actions'));
        $ilCtrl->setParameter($this->getParentObject(), 'file', $type . ':' . $filename);
        $list->addItem($this->lng->txt('download'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'download'));
        $ilCtrl->setParameter($this->getParentObject(), 'file', '');
        return $list->getHTML();
    }

    /**
     * @inheritdoc
     */
    public function numericOrdering(string $a_field): bool
    {
        if (in_array($a_field, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    /***
     *
     */
    protected function initMultiCommands(): void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }
}
