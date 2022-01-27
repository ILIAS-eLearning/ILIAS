<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \Psr\Http\Message;

/**
 * Content style UI trait for ui classes
 * @author Alexander Killing <killing@leifos.de>
 */
trait UI
{
    /**
     * @var UIFactory
     */
    protected $service_ui;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilTabsGUI
     */
    protected $tabs;

    /**
     * @var \ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ilLocatorGUI
     */
    protected $locator;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilHelpGUI
     */
    protected $help;

    /**
     * @var Message\RequestInterface
     */
    protected $request;

    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     *
     * @param
     * @return
     */
    protected function initUI(UIFactory $ui_factory)
    {
        $this->service_ui = $ui_factory;
        $this->ui = $this->service_ui->ui();
        $this->tabs = $this->service_ui->tabs();
        $this->toolbar = $this->service_ui->toolbar();
        $this->locator = $this->service_ui->locator();
        $this->ctrl = $this->service_ui->ctrl();
        $this->lng = $this->service_ui->lng();
        $this->help = $this->service_ui->help();
        $this->request = $this->service_ui->request();
        $this->refinery = $this->service_ui->refinery();
        $this->tpl = $this->service_ui->ui()->mainTemplate();
    }
}
