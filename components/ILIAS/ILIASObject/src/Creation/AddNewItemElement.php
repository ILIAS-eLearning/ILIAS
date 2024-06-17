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

namespace ILIAS\ILIASObject\Creation;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\Data\URI;

class AddNewItemElement
{
    /**
     * @param array<AddNewItemElement> $content
     */
    public function __construct(
        private readonly AddNewItemElementTypes $type,
        private readonly string $label,
        private readonly ?Icon $icon = null,
        private readonly ?URI $creation_uri = null,
        private readonly array $sub_elements = []
    ) {
    }

    public function getType(): AddNewItemElementTypes
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }

    public function getCreationUri(): URI
    {
        return $this->creation_uri;
    }

    /**
     * @return array<AddNewItemElement>
     */
    public function getSubElements(): array
    {
        return $this->sub_elements;
    }
}
