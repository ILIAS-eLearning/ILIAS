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
abstract class MembershipTableGUI extends ilTable2GUI
{
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
        parent::__construct($a_parent_obj);
        $this->dic = $dic;
        $this->options = $options;

        $this->initSettings();
        $this->initColumns();
        $this->initData();
    }


    /**
     *
     */
    protected function initSettings() : void
    {
        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_obj));
        $this->setId($this->options->getId());
        $this->setTitle($this->options->getTitle());
        $this->setDescription($this->options->getDescription());
        $this->setEnableHeader($this->options->isHeaderEnabled());
        $this->setShowRowsSelector($this->options->isRowsSelectorShown());
        $this->setDefaultOrderDirection($this->options->getDefaultOrderDirection());
        $this->setEnableNumInfo($this->options->isNumInfoEnabled());
        $this->setRowTemplate($this->getRowTemplatePath());
    }


    /**
     * @return mixed
     */
    abstract protected function initColumns() : void;


    /**
     * @return string
     */
    abstract protected function getRowTemplatePath() : string;


    /**
     *
     */
    abstract protected function initData() : void;
}