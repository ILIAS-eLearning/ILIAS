<?php

namespace ILIAS\Membership\Changelog\UI;

use ILIAS\DI\Container;
use ilTable2GUI;
use TableOptions;

/**
 * Class MembershipTableGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class MembershipHistoryTableGUI extends ilTable2GUI
{

    const ROW_TEMPLATE = './Services/Membership/templates/default/tpl.mem_history_row.html';

    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var TableOptions
     */
    protected $options;


    /**
     * ChangelogTableGUI constructor.
     *
     * @param                       $a_parent_obj
     * @param Container             $dic
     * @param TableOptions          $options
     */
    public function __construct($a_parent_obj, Container $dic, TableOptions $options)
    {
        $this->setId($options->getId());
        parent::__construct($a_parent_obj);
        $this->dic = $dic;
        $this->dic->language()->loadLanguageModule('membership');
        $this->options = $options;
        $this->initSettings();
        $this->initColumns();
        $this->initFilter();
        $this->initData();
    }


    /**
     *
     */
    protected function initSettings() : void
    {
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
        $this->setTitle($this->options->getTitle());
        $this->setDescription($this->options->getDescription());
        $this->setEnableHeader($this->options->isHeaderEnabled());
        $this->setShowRowsSelector($this->options->isRowsSelectorShown());
        $this->setDefaultOrderDirection($this->options->getDefaultOrderDirection());
        $this->setEnableNumInfo($this->options->isNumInfoEnabled());
        $this->setRowTemplate($this->getRowTemplatePath());

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->determineLimit();
        $this->determineOffsetAndOrder();
    }



    /**
     * @param $value
     */
    protected function parseColumnValue($value) : void
    {
        $this->tpl->setCurrentBlock('td');
        $this->tpl->setVariable('VALUE', $value);
        $this->tpl->parseCurrentBlock();
    }


    /**
     * @return string
     */
    protected function getRowTemplatePath() : string
    {
        return self::ROW_TEMPLATE;
    }

    /**
     * @return mixed
     */
    abstract protected function initColumns() : void;


    /**
     *
     */
    abstract protected function initData() : void;

}