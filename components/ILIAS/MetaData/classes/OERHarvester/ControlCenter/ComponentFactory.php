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

namespace ILIAS\MetaData\OERHarvester\ControlCenter;

use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Status;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactoryInterface;
use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;

class ComponentFactory implements ComponentFactoryInterface
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected DataFactory $data_factory,
        protected LinkFactoryInterface $link_factory,
        protected PresentationUtilities $presentation_utilities
    ) {
    }

    /**
     * @return array{0:MessageBox, 1:Prompt}
     */
    public function getButtonToControlCenter(
        Status $status,
        int $ref_id,
        int $obj_id,
        string $type
    ): array {
        $view_link = $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            ltrim($this->link_factory->getViewLink($ref_id, $obj_id, $type), '/')
        );
        $prompt = $this->ui_factory->prompt()->standard($view_link);
        $button = $this->ui_factory->button()->standard($this->getButtonLabel(), $prompt->getShowSignal());
        $message = $this->ui_factory->messageBox()->info($this->getStatusInfo($status))->withButtons([$button]);
        return [$message, $prompt];
    }

    protected function getButtonLabel(): string
    {
        return $this->presentation_utilities->txt('md_publishing_control_center');
    }

    protected function getStatusInfo(Status $status): string
    {
        $status_label = match ($status) {
            Status::UNPUBLISHED => $this->presentation_utilities->txt('md_publishing_status_unpublished'),
            Status::BLOCKED => $this->presentation_utilities->txt('md_publishing_status_blocked'),
            Status::UNDER_REVIEW => $this->presentation_utilities->txt('md_publishing_status_under_review'),
            Status::PUBLISHED => $this->presentation_utilities->txt('md_publishing_status_published')
        };
        return $this->presentation_utilities->txtFill('md_publishing_current_status', $status_label);
    }
}
