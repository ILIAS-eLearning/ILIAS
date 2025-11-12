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

namespace ILIAS\MediaObjects\OverviewGUI\Table;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ILIAS\Data\Factory as DataFactory;
use DateTimeImmutable;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;
use DateTimeZone;
use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\MediaObjects\InternalDomainService;

class Builder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected DataFactory $data_factory,
        protected SubObjectRetrieval $sub_object_retrieval,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, true);
    }

    protected function getId(): string
    {
        return 'mob_overview';
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt('mob_media_objects_overview');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new Retrieval(
            $this->sub_object_retrieval,
            $this->domain,
            $this->data_factory
        );
    }

    protected function transformRow(array $data_row): array
    {
        $data = [
            'id' => $data_row['id'],
            'title' => $data_row['title'] ?? '',
            'last_update' => (new DateTimeImmutable('@' . ($data_row['last_update'] ?? 0)))
                ->setTimezone(new DateTimeZone($this->domain->user()->getTimeZone())),
            'internal_usages' => $this->buildLinkListingFromData($data_row['internal_usages'] ?? []),
            'mep_usages' => $this->buildLinkListingFromData($data_row['mep_usages'] ?? []),
            'external_usages' => $this->buildLinkListingFromData($data_row['external_usages'] ?? [])
        ];
        if (($data_row['copyright_identifier'] ?? '') !== '') {
            $preset = $this->domain->learningObjectMetadata()->copyrightHelper()->getCopyrightPreset($data_row['copyright_identifier']);
            if ($image = $preset->presentAsImageOnly()) {
                $data['copyright_icon'] = $image;
            }
            if ($link = $preset->presentAsLinkOnly()) {
                $data['copyright'] = $link;
            }
        } elseif (($data_row['copyright'] ?? '') !== '') {
            $data['copyright'] = $this->gui->ui()->factory()->link()->standard(
                $data_row['copyright'],
                ''
            )->withDisabled();
        }
        return $data;
    }

    protected function buildLinkListingFromData(array $usage_data): UnorderedListing
    {
        $ui_factory = $this->gui->ui()->factory();

        $links = [];
        foreach ($usage_data as $usage) {
            $title = $usage['title'] ?? '';
            $link_string = $usage['link'] ?? '';
            if ($title === '') {
                continue;
            }
            $link = $ui_factory->link()->standard($title, $link_string);
            if ($link_string === '') {
                $link = $link->withDisabled();
            }
            $links[] = $link;
        }
        return $ui_factory->listing()->unordered($links);
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $lom = $this->domain->learningObjectMetadata();

        $table = $table
            ->textColumn('title', $lng->txt('mob'), true)
            ->dateColumn('last_update', $lng->txt('mob_last_update'), true);
        if ($lom->copyrightHelper()->isCopyrightSelectionActive()) {
            $table = $table
                ->iconColumn('copyright_icon', $lng->txt('mob_copyright_icon'), false)
                ->linkColumn('copyright', $lng->txt('mob_copyright'), true);
        }
        return $table
            ->linkListingColumn('internal_usages', $lng->txt('mob_internal_usages_in_object'))
            ->linkListingColumn('mep_usages', $lng->txt('mob_usages_in_media_pools'))
            ->linkListingColumn('external_usages', $lng->txt('mob_usages_in_other_objects'));
    }
}
