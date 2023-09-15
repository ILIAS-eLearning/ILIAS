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

use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldableInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;

interface ElementInterface extends BaseElementInterface, ScaffoldableInterface, MarkableInterface
{
    /**
     * @return ElementInterface[]
     */
    public function getSubElements(): \Generator;

    public function getSuperElement(): ?ElementInterface;

    public function isScaffold(): bool;

    public function getData(): DataInterface;
}
