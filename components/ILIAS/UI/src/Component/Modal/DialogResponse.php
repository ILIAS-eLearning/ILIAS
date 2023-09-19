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

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button;

/**
 *
 */
interface DialogResponse extends Component
{
    /**
     * Changes the title of the Modal
     */
    public function withTitle(string $title): self;

    /**
     * Changes the contents of the Modal
     */
    public function withContent(DialogContent ...$content): self;

    /**
     * Adds buttons to the bottom of the Modal
     */
    public function withButtons(Button\Button ...$buttons): self;

    /**
     * Tells the Modal to close
     */
    public function withCloseModal(bool $flag): self;

    /**
     * Provides a shorthand to a Standard Button that closes the Modal when clicked.
     * Use the Button in ::withButtons.
     */
    public function getCloseButton(string $label = 'Cancel'): Button\Standard;
}
