<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Button\Shy;

/**
 * Interface Card
 * @package ILIAS\UI\Component\Card
 */
interface Card extends Component, JavaScriptBindable, Clickable
{

    /**
     * Sets the title in the heading section of the card
     * @param string|Shy $title
     */
    public function withTitle($title) : Card;

    /**
     * Get the title in the heading section of the card
     * @return string|Shy
     */
    public function getTitle();

    /**
     * Get a Card like this with a title action
     * @param string|Signal[] $action
     */
    public function withTitleAction($action) : Card;

    /**
     * Returns the title action if given, otherwise null
     * @return string|Signal[]|null
     */
    public function getTitleAction();

    /**
     * Set multiple sections of the card as array
     * @param \ILIAS\UI\Component\Component[] $sections
     */
    public function withSections(array $sections) : Card;

    /**
     * Get the multiple sections of the card as array
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getSections() : array;

    /**
     * Set the image of the card
     */
    public function withImage(Image $image) : Card;

    /**
     * Get the image of the card
     */
    public function getImage() : ?Image;

    /**
     * Get a Card like this with a highlight
     */
    public function withHighlight(bool $status) : Card;

    /**
     * Returns whether the Card is highlighted
     */
    public function isHighlighted() : bool;
}
