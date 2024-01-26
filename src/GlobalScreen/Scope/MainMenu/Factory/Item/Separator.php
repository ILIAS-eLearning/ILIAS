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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;

/**
 * Class Separator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Separator extends AbstractChildItem implements hasTitle, isChild
{
    /**
     * @var bool
     */
    protected $visible_title = false;
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @param string $title
     * @return Separator
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    public function withVisibleTitle(bool $visible_title) : self
    {
        $clone = clone($this);
        $clone->visible_title = $visible_title;

        return $clone;
    }

    /**
     * @return bool
     */
    public function isTitleVisible() : bool
    {
        return $this->visible_title;
    }
}
