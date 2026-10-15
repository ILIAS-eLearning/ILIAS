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

use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Services;
use ILIAS\UI\Component\Item\Standard;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\components\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\components\ResourceStorage\Collections\View\PreviewDefinition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RevisionToComponent extends BaseToComponent implements ToComponent
{
    private Information $information;
    private Services $irss;
    private PreviewDefinition $preview_definition;

    public function __construct(
        private Revision $revision,
        ?ActionGenerator $action_generator = null,
        private bool $confidential = false,
        private bool $redacted = false
    ) {
        global $DIC;
        parent::__construct($action_generator);
        $this->irss = $DIC->resourceStorage();
        $this->information = $this->revision->getInformation();
        $this->preview_definition = new PreviewDefinition();
    }

    public function getAsItem(bool $with_image): Standard
    {
        $properties = array_merge(
            $this->getCommonProperties(),
            $this->getDetailedProperties()
        );
        $item = $this->ui_factory->item()->standard($this->getRevisionTitle())
                                 ->withDescription($this->getInformationTitle())
                                 ->withProperties($properties);

        if ($with_image) {
            return $item->withLeadImage($this->getImage());
        }
        return $item;
    }

    public function getAsCard(): Card
    {
        return $this->ui_factory->card()->repositoryObject(
            $this->getInformationTitle(),
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
                ->withHeadline($this->getInformationTitle())
                ->withSubheadline($this->getRevisionTitle())
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
        if (!$this->redacted && $this->irss->flavours()->possible($this->revision->getIdentification(), $this->preview_definition)) {
            $flavour = $this->irss->flavours()->get($this->revision->getIdentification(), $this->preview_definition);
            $src = $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray()[0] ?? null;
        }

        return $this->ui_factory->image()->responsive(
            $src ?? $this->getPlaceholderImage(),
            $this->getInformationTitle()
        )->withAlt($this->getInformationTitle());
    }

    private function getInformationTitle(): string
    {
        return $this->redacted
            ? $this->language->txt('confidential_redacted')
            : $this->information->getTitle();
    }

    private function getRevisionTitle(): string
    {
        return $this->redacted
            ? $this->language->txt('confidential_redacted')
            : $this->revision->getTitle();
    }

    protected function getPlaceholderImage(): string
    {
        return './assets/images/placeholder/file_placeholder.svg';
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
        $properties = [
            $this->language->txt('file_size') => $this->formatSize($this->information->getSize()),
            $this->language->txt('type') => $this->information->getMimeType(),
        ];
        if ($this->confidential) {
            $properties[$this->language->txt('confidential')] = $this->language->txt('yes');
        }

        return $properties;
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
