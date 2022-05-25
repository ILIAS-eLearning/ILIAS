<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilExportTableGUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 * @ingroup ModulesTest
 */

class ilTestExportTableGUI extends ilExportTableGUI
{
    public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
    {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);

        // NOT REQUIRED ANYMORE, PROBLEM NOW FIXED IN THE ROOT
        // KEEP CODE, JF OPINIONS / ROOT FIXINGS CAN CHANGE
        //$this->addCustomColumn($this->lng->txt('actions'), $this, 'formatActionsList');
    }

    protected function formatActionsList(string $type, string $filename) : string
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($this->lng->txt('actions'));
        $ilCtrl->setParameter($this->getParentObject(), 'file', $filename);
        $list->addItem($this->lng->txt('download'), '', $ilCtrl->getLinkTarget($this->getParentObject(), 'download'));
        $ilCtrl->setParameter($this->getParentObject(), 'file', '');
        return $list->getHTML();
    }

    protected function initMultiCommands() : void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }

    /**
     * Overwrite method because data is passed from outside
     */
    public function getExportFiles() : array
    {
        return array();
    }

    protected function initColumns() : void
    {
        $this->addColumn($this->lng->txt(''), '', '1', true);
        $this->addColumn($this->lng->txt('file'), 'file');
        $this->addColumn($this->lng->txt('size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'timestamp');
    }

    public function numericOrdering(string $a_field) : bool
    {
        if (in_array($a_field, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    protected function getRowId(array $row) : string
    {
        return $row['file'];
    }
}
