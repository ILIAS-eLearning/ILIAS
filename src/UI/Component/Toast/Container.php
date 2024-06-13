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

namespace ILIAS\UI\Component\Toast;

use ILIAS\UI\Component\Component;

interface Container extends Component
{
    /**
     * @return Toast[]
     */
    public function getToasts(): array;

    public function withAdditionalToast(Toast $toast): Container;

    public function withoutToasts(): Container;

    /**
     * Create a copy of this container with a vanish time in miliseconds.
     * The vanish time defines the time after which the toasts vanish.
     */
    public function withVanishTime(int $vanishTime): Container;

    public function getVanishTime(): int;

    /**
     * Create a copy of this container with a delay time in miliseconds.
     * The delay time defines the time when the toasts are shown after a page refresh or an asyncronous update.
     */
    public function withDelayTime(int $delayTime): Container;

    public function getDelayTime(): int;
}
