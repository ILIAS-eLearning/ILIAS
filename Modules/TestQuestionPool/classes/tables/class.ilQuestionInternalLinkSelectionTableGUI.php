<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilQuestionInternalLinkSelectionTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilQuestionInternalLinkSelectionTableGUI extends ilTable2GUI
{
    /**
     * @param        $a_parent_obj
     * @param string $a_parent_cmd
     * @param string $a_template_context
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '', $a_template_context = '')
    {
        /**
         * @var $ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setId('qst_ils_' . $a_parent_obj->object->getId() . '_' . substr(md5($a_template_context), 0, 6));
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->setRowTemplate('tpl.il_as_qpl_question_internal_link_selection_table_row.html', 'Modules/TestQuestionPool');
        $this->setLimit(PHP_INT_MAX);

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), 'cancelExplorer'));

        $this->disable('hits');
        $this->disable('numinfo');
        $this->disable('header');

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('ASC');

        $this->addCommandButton('cancelExplorer', $this->lng->txt('cancel'));
        
        $this->initColumns();
    }

    /**
     *
     */
    protected function initColumns()
    {
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');
        $this->addColumn($this->lng->txt('link'), '');
    }
}
