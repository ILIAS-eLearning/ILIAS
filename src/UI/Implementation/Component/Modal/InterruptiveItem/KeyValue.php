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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component\Modal\InterruptiveItem\KeyValue as KeyValueInterface;

class KeyValue extends InterruptiveItem implements KeyValueInterface
{
    protected string $key;
    protected string $value;

    public function __construct(
        string $id,
        string $key,
        string $value
    ) {
        parent::__construct($id);

        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
