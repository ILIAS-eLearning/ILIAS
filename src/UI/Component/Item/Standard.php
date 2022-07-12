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
 
namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Image\Image;
use \ILIAS\UI\Component\Player\Audio;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\Data\Color;
use ILIAS\UI\Component\Dropdown\Standard as DropdownStandard;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

/**
 * Interface Standard Item
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Standard extends Item
{
    /**
     * Set a color
     */
    public function withColor(Color $color) : Standard;

    /**
     * Return the given color
     */
    public function getColor() : ?Color ;

    /**
     * Set image as lead
     */
    public function withLeadImage(Image $image) : Standard;

    /**
     * Set audio player
     */
    public function withAudioPlayer(Audio $audio) : Standard;

    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $icon) : Standard;

    /**
     * Set avatar as lead
     */
    public function withLeadAvatar(Avatar $avatar) : Standard;

    /**
     * Set image as lead
     */
    public function withLeadText(string $text) : Standard;

    /**
     * Reset lead to null
     */
    public function withNoLead() : Standard;

    /**
     * @return null|string|Image|Icon|Avatar
     */
    public function getLead();

    public function getAudioPlayer() : ?Audio;

    /**
     * Set progress meter chart
     */
    public function withProgress(ProgressMeter $chart) : Standard;

    public function getProgress() : ?ProgressMeter;

    /**
     * Create a new appointment item with a set of actions to perform on it.
     */
    public function withActions(DropdownStandard $actions) : Standard;

    /**
     * Get the actions of the item.
     */
    public function getActions() : ?DropdownStandard;
}
