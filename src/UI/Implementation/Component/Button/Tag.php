<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\Data as D;

class Tag extends Button implements C\Button\Tag
{
    private static $relevance_levels = array(
         self::REL_VERYLOW,
         self::REL_LOW,
         self::REL_MID,
         self::REL_HIGH,
         self::REL_VERYHIGH
    );

    /**
     * @var int
     */
    protected $relevance = self::REL_VERYHIGH;

    /**
     * @var Color
     */
    protected $bgcol;

    /**
     * @var Color
     */
    protected $forecol;

    /**
     * @var string[]
     */
    protected $additional_classes;

    /**
     * @inheritdoc
     */
    public function withRelevance($relevance)
    {
        $this->checkStringArg('relevance', $relevance);
        $this->checkArgIsElement('relevance', $relevance, self::$relevance_levels, 'relevance');
        $clone = clone $this;
        $clone->relevance = $relevance;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * @inheritdoc
     */
    public function getRelevanceClass()
    {
        return self::$relevance_levels[$this->relevance - 1];
    }

    /**
     * @inheritdoc
     */
    public function withBackgroundColor(\ILIAS\Data\Color $col)
    {
        $this->checkArgInstanceOf('Color', $col, \ILIAS\Data\Color::class);
        $clone = clone $this;
        $clone->bgcol = $col;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getBackgroundColor()
    {
        return $this->bgcol;
    }

    /**
     * @inheritdoc
     */
    public function withForegroundColor(\ILIAS\Data\Color $col)
    {
        $this->checkArgInstanceOf('Color', $col, \ILIAS\Data\Color::class);
        $clone = clone $this;
        $clone->forecol = $col;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getForegroundColor()
    {
        if (is_null($this->forecol) && is_null($this->bgcol) === false) {
            $col_val = $this->bgcol->isDark() ? '#fff' : '#000';
            $df = new D\Factory();
            return $df->color($col_val);
        }
        return $this->forecol;
    }

    /**
     * @inheritdoc
     */
    public function withClasses($classes)
    {
        $classes = $this->toArray($classes);
        foreach ($classes as $class) {
            $this->checkStringArg('classes', $class);
        }
        $clone = clone $this;
        $clone->additional_classes = $classes;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getClasses()
    {
        if (!$this->additional_classes) {
            return array();
        }
        return $this->additional_classes;
    }
}
