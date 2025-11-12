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

namespace ILIAS\Search\Presentation\Result\UI;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Item\Item;
use ILIAS\UI\Component\Panel\Listing\Listing as ListingPanel;
use DateTimeImmutable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\ViewControl\Pagination;
use ILIAS\UI\Component\ViewControl\Sortation as SortationViewControl;
use ILIAS\UI\Factory as UIFactory;
use ilLanguage;
use ilDateTime;
use ilDatePresentation;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\Search\Presentation\Result\Sortation;
use ILIAS\Search\Presentation\Result\ViewControlInfos;
use ILIAS\Search\GUI\Param;

class ComponentFactoryImpl implements ComponentFactory
{
    protected const int MAX_DESCRIPTION_LENGTH = 128;

    public function __construct(
        protected UIFactory $ui_factory,
        protected ilLanguage $lng,
        protected Sanitizer $sanitizer
    ) {
    }

    public function getPanel(
        ViewControlInfos $view_control_infos,
        Item ...$items
    ): ListingPanel {
        $item_group = $this->ui_factory->item()->group('', $items);
        $view_controls = [
            $this->getPaginationViewControl(
                $view_control_infos->currentPage(),
                $view_control_infos->maxPages(),
                $view_control_infos->pageSize(),
                $view_control_infos->paginationAction(),
                $view_control_infos->pageParam()
            ),
            $this->getSortationViewControl(
                $view_control_infos->sortation(),
                $view_control_infos->sortationAction(),
                $view_control_infos->sortationParam()
            )
        ];

        return $this->ui_factory->panel()->listing()->standard(
            $this->lng->txt("search_results"),
            [$item_group]
        )->withViewControls($view_controls);
    }

    protected function getPaginationViewControl(
        int $current_page,
        int $max_pages,
        int $page_size,
        URI $action,
        Param $page_param
    ): Pagination {
        // pages in the view control are 0-indexed, in search 1-indexed
        return $this->ui_factory->viewControl()->pagination()
                                ->withTargetURL((string) $action, $page_param->value)
                                ->withCurrentPage($current_page - 1)
                                ->withPageSize($page_size)
                                ->withTotalEntries($page_size * $max_pages);
    }

    protected function getSortationViewControl(
        Sortation $sortation,
        URI $action,
        Param $sortation_param
    ): SortationViewControl {
        $options = [
            Sortation::RELEVANCE_DESC->value => $this->lng->txt('search_sort_relevance'),
            Sortation::TITLE_ASC->value => $this->lng->txt('search_sort_title_asc'),
            Sortation::TITLE_DESC->value => $this->lng->txt('search_sort_title_desc'),
            Sortation::CREATION_DATE_DESC->value => $this->lng->txt('search_sort_creation_date_desc'),
            Sortation::CREATION_DATE_ASC->value => $this->lng->txt('search_sort_creation_date_asc')
        ];

        return $this->ui_factory->viewControl()
                                ->sortation($options, $sortation->value)
                                ->withLabelPrefix($this->lng->txt('search_sort_by'))
                                ->withTargetURL((string) $action, $sortation_param->value);
    }

    public function getModalForSubitems(
        string $object_title,
        int $items_per_page,
        bool $show_too_many_items_warning,
        Item ...$items
    ): ?Modal {
        if ($items === []) {
            return null;
        }

        if ($show_too_many_items_warning) {
            $items[] = $this->ui_factory->item()->shy($this->lng->txt('search_results_too_many_subitems'));
        }

        $title = sprintf(
            $this->lng->txt('search_detailed_results_title'),
            $this->sanitizer->sanitize($object_title)
        );

        $pages = [];
        foreach (array_chunk($items, $items_per_page) as $chunk_of_items) {
            $card = $this->ui_factory->card()->standard($title)->withSections($chunk_of_items);
            $pages[] = $this->ui_factory->modal()->lightboxCardPage($card);
        }
        return $this->ui_factory->modal()->lightbox($pages);
    }

    public function getItemForObject(
        string $type_icon_path,
        string $type_icon_label,
        string $title,
        ?URI $link,
        string $description,
        string $content,
        string $path,
        DateTimeImmutable $created_on,
        string $copyright,
        ?Signal $subitem_show_signal
    ): Item {
        $item_title = $this->sanitizer->sanitizeAndSetUpPlaceholders($title);
        if ($link !== null) {
            $item_title = $this->ui_factory->link()->standard($item_title, (string) $link);
        }

        $type_icon = $this->ui_factory->symbol()->icon()->custom(
            $type_icon_path,
            $this->sanitizer->sanitize($type_icon_label)
        );

        $properties = [
            $this->lng->txt('path') => $this->sanitizer->sanitize($path),
            $this->lng->txt('create_date') => $this->formatDate($created_on)
        ];
        if ($copyright !== '') {
            $properties[$this->lng->txt('search_copyright')] = $this->sanitizer->sanitize($copyright);
        }
        if ($description !== '') {
            if (mb_strlen($description) >= self::MAX_DESCRIPTION_LENGTH) {
                $description = mb_substr($description, 0, self::MAX_DESCRIPTION_LENGTH) . '...';
            }
            $properties[$this->lng->txt('description')] = $this->sanitizer->sanitizeAndSetUpPlaceholders($description);
        }

        $item = $this->ui_factory->item()->standard($item_title)
                                 ->withLeadIcon($type_icon);
        if ($subitem_show_signal !== null) {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt('search_results_show_subitems'),
                $subitem_show_signal
            );
            $item = $item->withMainAction($button);
        }
        return $item->withDescription($this->sanitizer->sanitizeAndSetUpPlaceholders($content))
                    ->withProperties($properties);
    }

    protected function formatDate(DateTimeImmutable $date): string
    {
        $relative = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(true);
        $res = ilDatePresentation::formatDate(new ilDateTime($date->getTimestamp(), IL_CAL_UNIX));
        ilDatePresentation::setUseRelativeDates($relative);
        return $res;
    }

    public function getItemForSubitem(
        string $title,
        ?URI $link,
        bool $open_link_in_new_viewport,
        string $content,
        string $type,
        string $copyright
    ): Item {
        $item_title = $this->sanitizer->sanitizeAndSetUpPlaceholders($title);
        if ($link !== null) {
            $item_title = $this->ui_factory->link()->standard($item_title, (string) $link)
                                                   ->withOpenInNewViewport($open_link_in_new_viewport);
        }
        $properties = [$this->lng->txt('type') => $this->sanitizer->sanitize($type)];
        if ($copyright !== '') {
            $properties[$this->lng->txt('search_copyright')] = $this->sanitizer->sanitize($copyright);
        }
        return $this->ui_factory->item()->standard($item_title)
                                ->withDescription($this->sanitizer->sanitizeAndSetUpPlaceholders($content))
                                ->withProperties($properties);
    }
}
