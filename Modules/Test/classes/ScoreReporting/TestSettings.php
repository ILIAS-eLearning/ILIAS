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

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Refinery\Factory as Refinery;

abstract class TestSettings
{
    protected int $test_id;

    public function __construct(int $test_id)
    {
        $this->test_id = $test_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }
    public function withTestId(int $test_id): self
    {
        $clone = clone $this;
        $clone->test_id = $test_id;
        return $clone;
    }

    abstract public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): Input;

    abstract public function toStorage(): array;
}
