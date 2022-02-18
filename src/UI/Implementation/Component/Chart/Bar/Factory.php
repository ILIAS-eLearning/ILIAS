<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 ********************************************************************
 */

namespace ILIAS\UI\Implementation\Component\Chart\Bar;

use ILIAS\Data\Chart\Dataset;

class Factory implements \ILIAS\UI\Component\Chart\Bar\Factory
{
    public function vertical(
        string $id,
        string $title,
        Dataset $dataset,
        array $bars
    ) : Vertical {
        return new Vertical($id, $title, $dataset, $bars);
    }

    public function horizontal(
        string $id,
        string $title,
        Dataset $dataset,
        array $bars
    ) : Horizontal {
        return new Horizontal($id, $title, $dataset, $bars);
    }
}
