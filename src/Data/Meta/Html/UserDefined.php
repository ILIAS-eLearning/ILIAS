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
 */

declare(strict_types=1);

namespace ILIAS\Data\Meta\Html;

use Generator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class UserDefined extends Tag
{
    public function __construct(
        protected string $key,
        protected string $value
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toHtml(): string
    {
        return "<meta name=\"$this->key\" content=\"$this->value\" />";
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
