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
use ILIAS\Repository\Modal\ModalAdapterGUI;
use Slim\Http\Stream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Repository\Filter\FilterAdapterGUI;
use ILIAS\Repository\Button\ButtonAdapterGUI;
use ILIAS\Repository\Link\LinkAdapterGUI;
use ILIAS\Repository\Symbol\SymbolAdapterGUI;
use ILIAS\Repository\Listing\ListingAdapterGUI;
use ILIAS\Repository\HTTP\HTTPUtil;
use ILIAS\Repository\Profile\ProfileGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait GlobalDICGUIServices
{
    private \ILIAS\DI\Container $DIC;

    protected function initGUIServices(\ILIAS\DI\Container $DIC): void
    {
        $this->DIC = $DIC;
        FormAdapterGUI::initJavascript();
    }

    public function ui(): UIServices
    {
        return $this->DIC->ui();
    }

    public function object(): \ilObjectService
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

    public function httpUtil(): HTTPUtil
    {
        return new HTTPUtil($this->DIC->http());
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

    public function navigationHistory(): \ilNavigationHistory
    {
        return $this->DIC["ilNavigationHistory"];
    }

    /**
     * @param array|string $class_path
     */
    public function form(
        $class_path,
        string $cmd,
        string $submit_caption = ""
    ): FormAdapterGUI {
        return new FormAdapterGUI(
            $class_path,
            $cmd,
            $submit_caption
        );
    }

    public function modal(
        string $title = "",
        string $cancel_label = ""
    ): ModalAdapterGUI {
        return new ModalAdapterGUI(
            $title,
            $cancel_label,
            $this->httpUtil()
        );
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function send(string $output): void
    {
        $http = $this->http();
        $http->saveResponse($http->response()->withBody(
            Streams::ofString($output)
        ));
        $http->sendResponse();
        $http->close();
    }

    /**
     * @param array|string $class_path
     */
    public function filter(
        string $filter_id,
        $class_path,
        string $cmd,
        bool $activated = true,
        bool $expanded = true
    ): FilterAdapterGUI {
        return new FilterAdapterGUI(
            $filter_id,
            $class_path,
            $cmd,
            $activated,
            $expanded
        );
    }

    public function button(
        string $caption,
        string $cmd
    ): ButtonAdapterGUI {
        return new ButtonAdapterGUI(
            $caption,
            $cmd
        );
    }

    public function link(
        string $caption,
        string $href,
        bool $new_viewport = false
    ): LinkAdapterGUI {
        return new LinkAdapterGUI(
            $caption,
            $href,
            $new_viewport
        );
    }

    public function symbol(
    ): SymbolAdapterGUI {
        return new SymbolAdapterGUI(
        );
    }

    public function listing(
    ): ListingAdapterGUI {
        return new ListingAdapterGUI(
        );
    }

    public function profile(): ProfileGUI
    {
        return new ProfileGUI(
            $this->DIC->repository()->internal()->domain()->profile(),
            $this->ui()->factory()
        );
    }
}
