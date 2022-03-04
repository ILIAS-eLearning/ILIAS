<?php declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\Data\Dimension;

use ILIAS\Data\Dimension;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Factory
{
    public function cardinal(array $value_labels = []) : CardinalDimension
    {
        return new CardinalDimension($value_labels);
    }

    public function range(CardinalDimension $cardinal_dimension) : RangeDimension
    {
        return new RangeDimension($cardinal_dimension);
    }
}
