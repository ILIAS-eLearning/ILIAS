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

namespace ILIAS\components\ResourceStorage\Resources\UI;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\components\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\ExtractPages;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\components\ResourceStorage\Collections\View\PreviewDefinition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RevisionToComponent extends BaseToComponent implements ToComponent
{
    private \ILIAS\ResourceStorage\Information\Information $information;
    private \ILIAS\ResourceStorage\Services $irss;
    private $preview_definition;

    public function __construct(
        private Revision $revision,
        ?ActionGenerator $action_generator = null
    ) {
        global $DIC;
        parent::__construct($action_generator);
        $this->irss = $DIC->resourceStorage();
        $this->information = $this->revision->getInformation();
        $this->preview_definition = new PreviewDefinition();
    }

    public function getAsItem(bool $with_image): \ILIAS\UI\Component\Item\Standard
    {
        $properties = array_merge(
            $this->getCommonProperties(),
            $this->getDetailedProperties()
        );
        $item = $this->ui_factory->item()->standard($this->revision->getTitle())
                                 ->withDescription($this->information->getTitle())
                                 ->withProperties($properties);

        if ($with_image) {
            $item = $item->withLeadImage($this->getImage());
        }
        return $item;
    }

    public function getAsCard(): Card
    {
        return $this->ui_factory->card()->repositoryObject(
            $this->information->getTitle(),
            $this->getImage()
        )->withSections([$this->ui_factory->listing()->descriptive($this->getCommonProperties())]);
    }

    public function getAsRowMapping(): \Closure
    {
        return function (
            PresentationRow $row,
            ResourceIdentification $resource_identification
        ): PresentationRow {
            $actions = $this->action_generator->getActionsForRevision($this->revision);
            if ($actions !== []) {
                $row = $row->withAction(
                    $this->ui_factory->dropdown()->standard(
                        $actions
                    )
                );
            }

            return $row
                ->withHeadline($this->information->getTitle())
                ->withSubheadline($this->revision->getTitle())
                ->withImportantFields($this->getImportantProperties())
                ->withContent(
                    $this->ui_factory->listing()->descriptive($this->getCommonProperties())
                )
                ->withFurtherFields(
                    $this->getDetailedProperties()
                );
        };
    }

    private function getImage(): Image
    {
        // We could use Flavours in the Future
        $src = null;
        if ($this->irss->flavours()->possible($this->revision->getIdentification(), $this->preview_definition)) {
            $flavour = $this->irss->flavours()->get($this->revision->getIdentification(), $this->preview_definition);
            $src = $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray()[0] ?? null;
        }

        return $this->ui_factory->image()->responsive(
            $src ?? $this->getPlaceholderImage(),
            $this->information->getTitle()
        )->withAlt($this->information->getTitle());
    }

    protected function getPlaceholderImage(): string
    {
        return './templates/default/images/placeholder/file_placeholder.svg';
    }

    public function getImportantProperties(): array
    {
        return [
            $this->formatDate($this->information->getCreationDate()),
            $this->formatSize($this->information->getSize()),
        ];
    }

    public function getCommonProperties(): array
    {
        return [
            $this->language->txt('file_size') => $this->formatSize($this->information->getSize()),
            $this->language->txt('type') => $this->information->getMimeType(),

        ];
    }

    public function getDetailedProperties(): array
    {
        return [
            $this->language->txt('create_date') => $this->formatDate($this->information->getCreationDate()),
            $this->language->txt('revision_status') => $this->language->txt(
                'revision_status_' . $this->revision->getStatus()->value
            ),
        ];
    }
}
