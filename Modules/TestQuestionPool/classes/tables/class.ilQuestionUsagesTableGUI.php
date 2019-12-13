<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/Tree/classes/class.ilPathGUI.php';
require_once 'Services/Link/classes/class.ilLink.php';

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
     * @param ilUnitConfigurationGUI $controller
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
    protected function initColumns()
    {
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('author'), 'author');
        $this->addColumn($this->lng->txt('path'), '');
    }

    /**
     *
     */
    protected function initData()
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
                    'title'      => $instance['title'],
                    'author'     => $instance['author'],
                    'ref_id'     => $ref_id,
                    'is_trashed' => $trashed,
                    'path'       => $trashed ? $this->lng->txt('deleted') : $path->getPath(ROOT_FOLDER_ID, $ref_id)
                );
            }
        }
        $this->setData($rows);
    }

    /**
     * @param array $row
     */
    public function fillRow($row)
    {
        /**
         * @var $ilAccess ilAccessHandler
         */
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $this->tpl->setVariable('USAGE_INSTANCE_TITLE', $row['title']);
        $this->tpl->setVariable('USAGE_AUTHOR', $row['author']);
        $this->tpl->setVariable('USAGE_PATH', $row['path']);

        if ($ilAccess->checkAccess('read', '', $row['ref_id']) && !$row['is_trashed']) {
            $link = new ilLink();

            $this->tpl->setVariable('USAGE_INSTANCE_LINKTED_TITLE', $row['title']);
            $this->tpl->setVariable('USAGE_INSTANCE_HREF', $link->_getStaticLink($row['ref_id'], 'tst'));

            $this->tpl->setCurrentBlock('linked_title_b');
            $this->tpl->touchBlock('linked_title_b');
            $this->tpl->parseCurrentBlock();
        }
    }
}
