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
 * Class ilQuestionUsagesTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilQuestionUsagesTableGUI extends ilTable2GUI
{
    /**
     * @var assQuestion
     */
    protected $question;

    /**
     * @param string                 $cmd
     * @param string                 $template_context
     * @param assQuestion            $question
     */
    public function __construct($controller, $cmd, $template_context, assQuestion $question)
    {
        $this->question = $question;
        $this->setId('qst_usage_' . $question->getId());
        parent::__construct($controller, $cmd);

        $this->setRowTemplate('tpl.il_as_qpl_question_usage_table_row.html', 'Modules/TestQuestionPool');
        $this->setLimit(PHP_INT_MAX);

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('ASC');

        $this->setTitle($this->lng->txt('question_instances_title'));

        $this->disable('sort');
        $this->disable('hits');
        $this->disable('numinfo');

        $this->initColumns();
        $this->initData();
    }

    /**
     *
     */
    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('author'), 'author');
        $this->addColumn($this->lng->txt('path'), '');
    }

    /**
     *
     */
    protected function initData(): void
    {
        /**
         * @var $tree ilTree
         */
        global $DIC;
        $tree = $DIC['tree'];

        $path = new ilPathGUI();

        $rows = array();
        foreach ($this->question->getInstances() as $instance) {
            foreach ($instance['refs'] as $ref_id) {
                $trashed = $tree->isDeleted($ref_id);
                $rows[] = array(
                    'title' => $instance['title'],
                    'author' => $instance['author'],
                    'ref_id' => $ref_id,
                    'is_trashed' => $trashed,
                    'path' => $trashed ? $this->lng->txt('deleted') : $path->getPath(ROOT_FOLDER_ID, (int) $ref_id)
                );
            }
        }
        $this->setData($rows);
    }

    /**
     * @param array $a_set
     */
    public function fillRow(array $a_set): void
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $this->tpl->setVariable('USAGE_INSTANCE_TITLE', $a_set['title']);
        $this->tpl->setVariable('USAGE_AUTHOR', $a_set['author']);
        $this->tpl->setVariable('USAGE_PATH', $a_set['path']);

        if ($ilAccess->checkAccess('read', '', $a_set['ref_id']) && !$a_set['is_trashed']) {
            $link = new ilLink();

            $this->tpl->setVariable('USAGE_INSTANCE_LINKTED_TITLE', $a_set['title']);
            $this->tpl->setVariable('USAGE_INSTANCE_HREF', $link->_getStaticLink($a_set['ref_id'], 'tst'));

            $this->tpl->setCurrentBlock('linked_title_b');
            $this->tpl->touchBlock('linked_title_b');
            $this->tpl->parseCurrentBlock();
        }
    }
}
