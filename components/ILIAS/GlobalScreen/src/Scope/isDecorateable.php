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

namespace ILIAS\GlobalScreen\Scope;

use Closure;
use ILIAS\GlobalScreen\isGlobalScreenItem;
use ILIAS\UI\Help\Topic;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface isDecorateable
{
    public function withTopics(Topic ...$topics): self;

    public function getTopics(): array;

    /**
     * @description Add a Closure to decorate the Component using withAdditionalOnloadCode
     * @deprecated  Use addTriggererDecorator instead in Items which support it (more precise to the triggerer inside the MainBar/MetaBar)
     */
    public function addComponentDecorator(Closure $component_decorator): self;

    public function getComponentDecorator(): ?Closure;
}
