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

namespace ILIAS\Search\Presentation\Result\Subitem;

use ILIAS\Data\URI;
use ILIAS\Search\Presentation\Result\UI\ComponentFactory;

class PropertiesImpl implements Properties
{
    public function __construct(
        protected ID $id,
        protected string $title,
        protected ?URI $link,
        protected bool $open_link_in_new_viewport,
        protected string $presentable_subitem_type
    ) {
    }

    public function id(): ID
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function link(): ?URI
    {
        return $this->link;
    }

    public function openLinkInNewViewport(): bool
    {
        return $this->open_link_in_new_viewport;
    }

    public function presentableSubitemType(): string
    {
        return $this->presentable_subitem_type;
    }
}
