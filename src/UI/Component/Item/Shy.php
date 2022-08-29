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

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Shy
 * @package ILIAS\UI\Component\Item
 */
interface Shy extends Item
{
    /**
     * Get a copy of that shy with a close button.
     */
    public function withClose(Close $close): Shy;

    public function getClose(): ?Close;

    /**
     * Get a copy of that shy with a lead icon.
     */
    public function withLeadIcon(Icon $lead): Shy;

    public function getLeadIcon(): ?Icon;
}
