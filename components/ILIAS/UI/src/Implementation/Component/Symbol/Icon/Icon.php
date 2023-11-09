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

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

abstract class Icon implements C\Symbol\Icon\Icon
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var	string[]
     */
    protected static array $possible_sizes = array(
        self::SMALL,
        self::MEDIUM,
        self::LARGE,
        self::RESPONSIVE
    );

    protected string $name;
    protected string $label;
    protected string $size;
    protected ?string $abbreviation = null;
    protected bool $is_disabled;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withAbbreviation(string $abbreviation): C\Symbol\Icon\Icon
    {
        $clone = clone $this;
        $clone->abbreviation = $abbreviation;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * @inheritdoc
     */
    public function withSize(string $size): C\Symbol\Icon\Icon
    {
        $this->checkArgIsElement(
            "size",
            $size,
            self::$possible_sizes,
            implode('/', self::$possible_sizes)
        );
        $clone = clone $this;
        $clone->size = $size;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function withDisabled(bool $is_disabled): C\Symbol\Icon\Icon
    {
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }
}
