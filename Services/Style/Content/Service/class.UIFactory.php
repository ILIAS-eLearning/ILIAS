<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \Psr\Http\Message;

/**
 * Content style internal ui factory
 * @author Alexander Killing <killing@leifos.de>
 */
class UIFactory
{
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
     * @var CharacteristicUIFactory
     */
    protected $characteristic;

    /**
     * @var ImageUIFactory
     */
    protected $image;

    /**
     * @var Message\RequestInterface
     */
    protected $request;

    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;

    /**
     * UIFactory constructor.
     * @param \ILIAS\DI\UIServices     $ui
     * @param \ilTabsGUI               $tabs
     * @param \ilToolbarGUI            $toolbar
     * @param \ilLocatorGUI            $locator
     * @param \ilCtrl                  $ctrl
     * @param \ilLanguage              $lng
     * @param \ilHelpGUI               $help
     * @param Message\RequestInterface $request
     * @param \ILIAS\Refinery\Factory  $refinery
     */
    public function __construct(
        \ILIAS\DI\UIServices $ui,
        \ilTabsGUI $tabs,
        \ilToolbarGUI $toolbar,
        \ilLocatorGUI $locator,
        \ilCtrl $ctrl,
        \ilLanguage $lng,
        \ilHelpGUI $help,
        Message\RequestInterface $request,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->ui = $ui;
        $this->tabs = $tabs;
        $this->toolbar = $toolbar;
        $this->locator = $locator;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
        $this->help = $help;
        $this->request = $request;
        $this->refinery = $refinery;
        $this->characteristic = new CharacteristicUIFactory(
            $this
        );
        $this->image = new ImageUIFactory(
            $this
        );
    }

    /**
     * @return CharacteristicUIFactory
     */
    public function characteristic(
    ) : CharacteristicUIFactory {
        return $this->characteristic;
    }

    /**
     * @return ImageUIFactory
     */
    public function image(
    ) : ImageUIFactory {
        return $this->image;
    }

    /**
     * @return \ILIAS\DI\UIServices
     */
    public function ui() : \ILIAS\DI\UIServices
    {
        return $this->ui;
    }

    /**
     * @return \ilTabsGUI
     */
    public function tabs() : \ilTabsGUI
    {
        return $this->tabs;
    }

    /**
     * @return \ilToolbarGUI
     */
    public function toolbar() : \ilToolbarGUI
    {
        return $this->toolbar;
    }

    /**
     * @return \ilLocatorGUI
     */
    public function locator() : \ilLocatorGUI
    {
        return $this->locator;
    }

    /**
     * @return \ilCtrl
     */
    public function ctrl() : \ilCtrl
    {
        return $this->ctrl;
    }

    /**
     * @return \ilLanguage
     */
    public function lng() : \ilLanguage
    {
        return $this->lng;
    }

    /**
     * @return \ilHelpGUI
     */
    public function help() : \ilHelpGUI
    {
        return $this->help;
    }

    /**
     * @return Message\RequestInterface
     */
    public function request() : Message\RequestInterface
    {
        return $this->request;
    }

    /**
     * @return \ILIAS\Refinery\Factory
     */
    public function refinery() : \ILIAS\Refinery\Factory
    {
        return $this->refinery;
    }
}
