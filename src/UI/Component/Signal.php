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

namespace ILIAS\UI\Component;

/**
 * Interface Signal
 *
 * A signal describes an event of a component which can be triggered by another component acting as triggerer.
 * For example, a modal offers signals for showing and closing itself. A button (which is a triggerer component)
 * can trigger the show signal of a modal on click, which will open the modal on button click.
 *
 * @package ILIAS\UI\Component
 */
interface Signal
{
    /**
     * Get the ID of this signal
     */
    public function getId(): string;

    /**
     * Get the options of this signal
     */
    public function getOptions(): array;
}
