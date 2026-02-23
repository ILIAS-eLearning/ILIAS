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
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\Search\Presentation\Result\ViewControlInfos;

interface ComponentFactory
{
    public function getPanel(
        ViewControlInfos $view_control_infos,
        Item ...$items
    ): ListingPanel;

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
    ): Item;

    public function getItemForSubitem(
        string $title,
        ?URI $link,
        bool $open_link_in_new_viewport,
        string $content,
        string $type,
        string $copyright
    ): Item;

    public function getModalForSubitems(
        string $object_title,
        int $items_per_page,
        bool $show_too_many_items_warning,
        Item ...$items
    ): ?Modal;
}
