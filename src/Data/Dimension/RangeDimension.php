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

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class RangeDimension extends Dimension
{
    protected CardinalDimension $cardinal_dimension;

    public function __construct(CardinalDimension $cardinal_dimension)
    {
        $this->cardinal_dimension = $cardinal_dimension;
        $this->value_labels = $this->cardinal_dimension->getLabels();
    }

    public function checkValue($value) : void
    {
        if (is_null($value)) {
            return;
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException(
                "Expected parameter to be null or an array with exactly two numeric parameters.
                            '$value' is given."
            );
        } elseif (count($value) !== 2) {
            throw new \InvalidArgumentException(
                "Expected parameter to be an array with exactly two numeric parameters."
            );
        } else {
            foreach ($value as $number) {
                $this->cardinal_dimension->checkValue($number);
            }
        }
    }
}
