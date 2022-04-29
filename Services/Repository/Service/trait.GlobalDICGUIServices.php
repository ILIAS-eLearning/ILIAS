<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Repository;

use ILIAS\DI\UIServices;
use ILIAS\HTTP;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait GlobalDICGUIServices
{
    protected UIServices $ui;
    protected \ilObjectServiceInterface $object_service;
    protected \ilCtrl $ctrl;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected HTTP\Services $http;
    protected FileUpload $upload;
    protected \ilToolbarGUI $toolbar;
    protected GlobalScreen\Services $global_screen;
    protected \ilHelpGUI $help;
    protected \ilTabsGUI $tabs;
    protected \ilLocatorGUI $locator;

    protected function initGUIServices(\ILIAS\DI\Container $DIC) : void
    {
        $this->ui = $DIC->ui();
        $this->object_service = $DIC->object();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->upload = $DIC->upload();
        $this->toolbar = $DIC->toolbar();
        $this->global_screen = $DIC->globalScreen();
        $this->help = $DIC->help();
        $this->tabs = $DIC->tabs();
        $this->locator = $DIC["ilLocator"];
    }

    public function ui() : UIServices
    {
        return $this->ui;
    }

    public function object() : \ilObjectServiceInterface
    {
        return $this->object_service;
    }

    public function ctrl() : \ilCtrl
    {
        return $this->ctrl;
    }

    public function http() : HTTP\Services
    {
        return $this->http;
    }

    public function mainTemplate() : \ilGlobalTemplateInterface
    {
        return $this->main_tpl;
    }

    public function upload() : FileUpload
    {
        return $this->upload;
    }

    public function toolbar() : \ilToolbarGUI
    {
        return $this->toolbar;
    }

    public function globalScreen() : GlobalScreen\Services
    {
        return $this->global_screen;
    }

    public function help() : \ilHelpGUI
    {
        return $this->help;
    }

    public function tabs() : \ilTabsGUI
    {
        return $this->tabs;
    }

    public function locator() : \ilLocatorGUI
    {
        return $this->locator;
    }
}
