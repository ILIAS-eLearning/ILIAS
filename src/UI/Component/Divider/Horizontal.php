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
 
namespace ILIAS\UI\Component\Divider;

use ILIAS\UI\Component\Component;

/**
 * Horizontal Divider
 */
interface Horizontal extends Component
{
    /**
     * Get the label of the divider
     */
    public function getLabel() : ?string;

    /**
     * Get a divider like this, but with another label
     */
    public function withLabel(string $label) : Horizontal;
}
