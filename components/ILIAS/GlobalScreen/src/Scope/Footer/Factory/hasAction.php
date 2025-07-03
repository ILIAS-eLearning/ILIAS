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

namespace ILIAS\GlobalScreen\Scope\Footer\Factory;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Signal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface hasAction extends isItem
{
    public function withAction(URI|Signal|string $action): self;

    public function getAction(): URI|Signal|string;

    public function withOpenInNewViewport(bool $state);

    public function mustOpenInNewViewport(): bool;
}
