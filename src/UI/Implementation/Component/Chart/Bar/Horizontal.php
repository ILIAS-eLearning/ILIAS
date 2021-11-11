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

namespace ILIAS\UI\Implementation\Component\Chart\Bar;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Chart\Bar\XAxis;
use ILIAS\Data\Chart\Dataset;

class Horizontal extends Bar implements C\Chart\Bar\Horizontal
{
    protected XAxis $x_axis;

    public function __construct(string $title, Dataset $dataset, array $bar_configs = [])
    {
        parent::__construct($title, $dataset, $bar_configs);
        $this->x_axis = new XAxis();
    }

    public function getIndexAxis() : string
    {
        return "y";
    }

    public function withCustomXAxis(XAxis $x_axis) : self
    {
        $clone = clone $this;
        $clone->x_axis = $x_axis;
        return $clone;
    }

    public function getXAxis() : XAxis
    {
        return $this->x_axis;
    }
}
