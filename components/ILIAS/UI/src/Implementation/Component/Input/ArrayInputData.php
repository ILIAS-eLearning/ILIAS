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

namespace ILIAS\UI\Implementation\Component\Input;

use LogicException;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ArrayInputData implements InputData
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get($name)
    {
        if (!$this->has($name)) {
            throw new LogicException("'$name' is not contained in provided data.");
        }

        return $this->data[$name];
    }

    public function getOr($name, $default)
    {
        return $this->data[$name] ?? $default;
    }

    public function has($name): bool
    {
        return array_key_exists($name, $this->data);
    }
}
