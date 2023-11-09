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

namespace ILIAS\MetaData\Elements;

use ILIAS\MetaData\Elements\Base\NullBaseElement;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Data\NullData;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Elements\Markers\NullMarker;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;

class NullElement extends NullBaseElement implements ElementInterface
{
    public function isScaffold(): bool
    {
        return false;
    }

    public function getData(): DataInterface
    {
        return new NullData();
    }

    public function isMarked(): bool
    {
        return false;
    }

    public function getMarker(): ?MarkerInterface
    {
        return new NullMarker();
    }

    public function mark(MarkerFactoryInterface $factory, Action $action, string $data_value = '')
    {
    }

    public function addScaffoldsToSubElements(ScaffoldProviderInterface $scaffold_provider): void
    {
    }

    public function addScaffoldToSubElements(ScaffoldProviderInterface $scaffold_provider, string $name): ?ElementInterface
    {
        return null;
    }

    public function getSubElements(): \Generator
    {
        yield from [];
    }

    public function getSuperElement(): ?ElementInterface
    {
        return null;
    }
}
