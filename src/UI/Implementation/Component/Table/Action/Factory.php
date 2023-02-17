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

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Table\Action as I;

class Factory implements I\Factory
{
    public function standard(string $label, string $parameter_name, Signal|URI $target): I\Standard
    {
        return new Standard($label, $parameter_name, $target);
    }

    public function single(string $label, string $parameter_name, Signal|URI $target): I\Single
    {
        return new Single($label, $parameter_name, $target);
    }

    public function multi(string $label, string $parameter_name, Signal|URI $target): I\Multi
    {
        return new Multi($label, $parameter_name, $target);
    }
}
