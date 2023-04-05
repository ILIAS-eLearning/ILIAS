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

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Modal\InterruptiveItem\InterruptiveItem;
use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * Interface Interruptive
 *
 * @package ILIAS\UI\Component\Modal
 */
interface Interruptive extends Standard, Modal
{
    /**
     * Get the message of this modal, displayed below the modals title
     */
    public function getMessage(): string;

    /**
     * Get the title of this modal
     */
    public function getTitle(): string;

    /**
     * Get the label of the cancel button in the footer
     */
    public function getCancelButtonLabel(): string;

    /**
     * Get a modal like this with the cancel button labeled
     * according to the parameter.
     * The label will be translated.
     */
    public function withCancelButtonLabel(string $cancel_label): Interruptive;

    /**
     * Return the affected items listed in the content by this modal
     *
     * @return InterruptiveItem[]
     */
    public function getAffectedItems(): array;
}
