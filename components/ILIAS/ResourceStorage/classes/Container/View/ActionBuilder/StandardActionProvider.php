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

declare(strict_types=1);

namespace ILIAS\components\ResourceStorage\Container\View;

use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ActionProvider;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\TopAction;
use ILIAS\Data\URI;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\SingleAction;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class StandardActionProvider implements ActionProvider
{
    /**
     * @var string
     */
    private const ACTION_UNZIP = 'unzip';
    /**
     * @var string
     */
    private const ACTION_DOWNLOAD = 'download';
    /**
     * @var string
     */
    private const ACTION_REMOVE = 'remove';

    private \ilLanguage $language;
    private \ilCtrlInterface $ctrl;
    private RoundTrip $add_directory_modal;
    private Factory $ui_factory;
    private bool $show_paths = false;

    public function __construct(
        private Request $request
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->language = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->initModals();
    }

    protected function initModals(): void
    {
        $this->add_directory_modal = $this->ui_factory->modal()->roundtrip(
            $this->language->txt('create_directory'),
            [],
            [
                $this->ui_factory->input()->field()->text(
                    $this->language->txt('directory_name'),
                    $this->language->txt('directory_name_info'),
                )->withRequired(true)
            ],
            $this->ctrl->getFormActionByClass(\ilContainerResourceGUI::class, \ilContainerResourceGUI::ADD_DIRECTORY)
        );
    }

    public function getAddDirectoryModal(): RoundTrip
    {
        return $this->add_directory_modal;
    }

    public function getComponents(): array
    {
        $components = [
            $this->add_directory_modal
        ];

        if ($this->show_paths) {
            $components[] = $this->ui_factory->messageBox()->info(
                implode('<br>', array_keys($this->request->getWrapper()->getData()))
            );
        }

        return $components;
    }

    public function getTopActions(): array
    {
        return [
            new TopAction(
                $this->language->txt('create_directory'),
                $this->add_directory_modal->getShowSignal()
            ),
            new TopAction(
                $this->language->txt('download_zip'),
                $this->buildURI(\ilContainerResourceGUI::CMD_DOWNLOAD_ZIP)
            ),
        ];
    }

    public function getSingleActions(Request $view_request): array
    {
        $single_actions = [
            self::ACTION_DOWNLOAD => new SingleAction(
                $this->language->txt(self::ACTION_DOWNLOAD),
                $this->buildURI(\ilContainerResourceGUI::CMD_DOWNLOAD),
                false,
                false,
                false
            )
        ];
        if ($view_request->canUserAdministrate()) {
            $single_actions[self::ACTION_REMOVE] = new SingleAction(
                $this->language->txt(self::ACTION_REMOVE),
                $this->buildURI(\ilContainerResourceGUI::CMD_RENDER_CONFIRM_REMOVE),
                true,
                true,
                true
            );

            $single_actions[self::ACTION_UNZIP] = new SingleAction(
                $this->language->txt(self::ACTION_UNZIP),
                $this->buildURI(\ilContainerResourceGUI::CMD_UNZIP),
                false,
                false,
                false,
                ['application/zip', 'application/x-zip-compressed']
            );
        }

        return $single_actions;
    }

    private function buildURI(
        string $command
    ): URI {
        return $this->retrieveURI(
            \ilContainerResourceGUI::class,
            $command
        );
    }

    public function retrieveURI(
        string $class,
        string $command
    ): URI {
        return new URI(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                $class,
                $command
            )
        );
    }

}
