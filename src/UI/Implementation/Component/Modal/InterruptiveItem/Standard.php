<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Modal\InterruptiveItem\Standard as StandardInterface;

class Standard extends InterruptiveItem implements StandardInterface
{
    protected string $title;
    protected string $description;
    protected ?Image $icon;

    public function __construct(
        string $id,
        string $title,
        Image $icon = null,
        string $description = ''
    ) {
        parent::__construct($id);

        $this->title = $title;
        $this->icon = $icon;
        $this->description = $description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIcon(): ?Image
    {
        return $this->icon;
    }
}
