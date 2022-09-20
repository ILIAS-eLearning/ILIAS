<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component;

/**
 * Class SignalGenerator
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class SignalGenerator implements SignalGeneratorInterface
{
    public const PREFIX = 'il_signal_';

    /**
     * @inheritdoc
     */
    public function create(string $class = ''): Signal
    {
        $id = $this->createId();
        return ($class) ? new $class($id) : new Signal($id);
    }

    protected function createId(): string
    {
        return str_replace(".", "_", uniqid(self::PREFIX, true));
    }
}
