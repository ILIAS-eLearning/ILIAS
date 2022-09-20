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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class InterruptiveItem
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveItem implements \ILIAS\UI\Component\Modal\InterruptiveItem
{
    use ComponentHelper;

    protected string $id;
    protected string $title;
    protected string $description;
    protected ?Image $icon;

    public function __construct(string $id, string $title, Image $icon = null, string $description = '')
    {
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->description = $description;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getIcon(): ?Image
    {
        return $this->icon;
    }
}
