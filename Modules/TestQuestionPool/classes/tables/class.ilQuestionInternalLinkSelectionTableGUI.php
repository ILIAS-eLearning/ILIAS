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
    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('description'), 'description');
        $this->addColumn($this->lng->txt('link'), '');
    }
}
