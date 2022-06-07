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
 *********************************************************************/
 
namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component\Link as L;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\Data\URI;

class Factory implements L\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(string $label, string $action) : L\Standard
    {
        return new Standard($label, $action);
    }

    /**
     * @inheritdoc
     */
    public function bulky(Symbol $symbol, string $label, URI $action) : L\Bulky
    {
        return new Bulky($symbol, $label, $action);
    }
}
