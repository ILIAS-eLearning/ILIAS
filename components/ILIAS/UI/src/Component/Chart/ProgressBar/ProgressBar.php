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

namespace ILIAS\UI\Component\Chart\ProgressBar;

use ILIAS\UI\Component\Signal;

interface ProgressBar extends \ILIAS\UI\Component\Component
{
    public function getMaximum(): float|int;

    public function getCurrent(): float|int;

    public function getCallback(): ProgressProvider;

    public function withCurrent(float|int $current): static;

//    public function withOnSuccess(Signal $signal): static;

//    public function withOnError(Signal $signal): static;

//    public function getProgressSignal(): ProgressSignal;

}
