<?php

declare(strict_types=1);

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

namespace ILIAS\Repository;

use ILIAS\DI\UIServices;
use ILIAS\HTTP;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen;
use ILIAS\Repository\Form\FormAdapterGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait GlobalDICGUIServices
{
    private \ILIAS\DI\Container $DIC;

    protected function initGUIServices(\ILIAS\DI\Container $DIC): void
    {
        $this->DIC = $DIC;
    }

    public function ui(): UIServices
    {
        return $this->DIC->ui();
    }

    public function object(): \ilObjectServiceInterface
    {
        return $this->DIC->object();
    }

    public function ctrl(): \ilCtrl
    {
        return $this->DIC->ctrl();
    }

    public function http(): HTTP\Services
    {
        return $this->DIC->http();
    }

    public function mainTemplate(): \ilGlobalTemplateInterface
    {
        return $this->DIC->ui()->mainTemplate();
    }

    public function upload(): FileUpload
    {
        return $this->DIC->upload();
    }

    public function toolbar(): \ilToolbarGUI
    {
        return $this->DIC->toolbar();
    }

    public function globalScreen(): GlobalScreen\Services
    {
        return $this->DIC->globalScreen();
    }

    public function help(): \ilHelpGUI
    {
        return $this->DIC->help();
    }

    public function tabs(): \ilTabsGUI
    {
        return $this->DIC->tabs();
    }

    public function locator(): \ilLocatorGUI
    {
        return $this->DIC["ilLocator"];
    }

    /**
     * @param array|string $class_path
     */
    public function form(
        $class_path,
        string $cmd
    ): FormAdapterGUI {
        return new FormAdapterGUI(
            $class_path,
            $cmd
        );
    }
}
