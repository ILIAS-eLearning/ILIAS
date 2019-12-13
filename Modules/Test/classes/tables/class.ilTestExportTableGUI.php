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
    protected $counter;
    protected $confirmdelete;

    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
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
    protected function formatActionsList($type, $filename)
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

    /***
     *
     */
    protected function initMultiCommands()
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }

    /**
     * Overwrite method because data is passed from outside
     */
    public function getExportFiles()
    {
        return array();
    }

    /**
     *
     */
    protected function initColumns()
    {
        $this->addColumn($this->lng->txt(''), '', '1', true);
        $this->addColumn($this->lng->txt('file'), 'file');
        $this->addColumn($this->lng->txt('size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'timestamp');
    }

    /**
     * @param string $column
     * @return bool
     */
    public function numericOrdering($column)
    {
        if (in_array($column, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    /**
     * @param array $row
     * @return string
     */
    protected function getRowId(array $row)
    {
        return $row['file'];
    }
}
