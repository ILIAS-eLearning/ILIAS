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

namespace ILIAS\MetaData\Repository\Validation\Data;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;

trait DataFetcher
{
    public function dataValue(
        ElementInterface $element,
        bool $ignore_marker
    ): string {
        if (
            !$ignore_marker &&
            ($element instanceof MarkableInterface) &&
            $element->isMarked()
        ) {
            return $element->getMarker()->dataValue();
        }
        return $element->getData()->value();
    }
}
