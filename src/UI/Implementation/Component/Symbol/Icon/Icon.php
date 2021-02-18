<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

abstract class Icon implements C\Symbol\Icon\Icon
{
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var	string
     */
    protected $name;

    /**
     * @var	string
     */
    protected $label;

    /**
     * @var	string
     */
    protected $size;

    /**
     * @var	string
     */
    protected $abbreviation;

    /**
     * @var bool
     */
    protected $is_disabled;

    /**
     * @var	string[]
     */
    protected static $possible_sizes = array(
        self::SMALL,
        self::MEDIUM,
        self::LARGE,
        self::RESPONSIVE
    );


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withAbbreviation($abbreviation)
    {
        $this->checkStringArg("string", $abbreviation);
        $clone = clone $this;
        $clone->abbreviation = $abbreviation;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @inheritdoc
     */
    public function withSize($size)
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
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function withDisabled($is_disabled)
    {
        $this->checkBoolArg("is_disabled", $is_disabled);
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }
}
