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

namespace ILIAS\Data\Chart;

use ILIAS\Data\Color;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class Bar
{
    protected ?string $color = null;
    protected ?float $size = null;

    public function __construct()
    {
    }

    public function withColor(Color $color) : self
    {
        $clone = clone $this;
        $clone->color = $color->asHex();
        return $clone;
    }

    public function getColor() : ?string
    {
        return $this->color;
    }
    
    public function withSize(float $relative_width) : self
    {
        $clone = clone $this;
        $clone->size = $relative_width;
        return $clone;
    }

    public function getSize() : ?float
    {
        return $this->size;
    }
}
