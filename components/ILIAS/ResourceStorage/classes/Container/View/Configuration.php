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

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\StorableContainerResource;
use ILIAS\Data\URI;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ExternalSingleAction;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Configuration
{
    private ExternalActionProvider $action_provider;
    private \ilCtrlInterface $ctrl;

    public function __construct(
        private StorableContainerResource $container,
        private ResourceStakeholder $stakeholder,
        private string $title,
        private Mode $mode = Mode::DATA_TABLE,
        private int $items_per_page = 100,
        private bool $user_can_upload = false,
        private bool $user_can_administrate = false,
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->action_provider = new ExternalActionProvider();
    }

    public function withExternalAction(
        string $label,
        string $target_gui,
        string $target_cmd,
        string $parameter_namespace,
        string $path_parameter = 'path',
        bool $supports_directories = false,
        array $supported_mime_types = ['*']
    ): self {
        $this->action_provider->addSingleAction(
            $target_gui . '_' . $target_cmd,
            new ExternalSingleAction(
                $label,
                $target_gui,
                $target_cmd,
                $path_parameter,
                $parameter_namespace,
                false,
                false,
                $supports_directories,
                $supported_mime_types
            )
        );

        return $this;
    }

    public function getContainer(): StorableContainerResource
    {
        return $this->container;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function canUserUpload(): bool
    {
        return $this->user_can_upload;
    }

    public function canUserAdministrate(): bool
    {
        return $this->user_can_administrate;
    }

    private function retrieveURI(
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

    public function getActionProvider(): ExternalActionProvider
    {
        return $this->action_provider;
    }

}
