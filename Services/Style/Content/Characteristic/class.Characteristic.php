<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * Characteristic (Class) of style
 * @author Alexander Killing <killing@leifos.de>
 */
class Characteristic
{
    /**
     * @var int
     */
    protected $style_id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $characteristic;

    /**
     * @var bool
     */
    protected $hide;

    /**
     * @var array key, is lang, value is title
     */
    protected $titles;

    /**
     * @var int
     */
    protected $order_nr;

    /**
     * @var bool
     */
    protected $outdated;

    /**
     * Characteristic constructor.
     * @param string $type
     * @param string $characteristic
     * @param bool   $hide
     * @param array  $titles
     * @param int    $order_nr
     * @param bool   $outdated
     */
    public function __construct(
        string $type,
        string $characteristic,
        bool $hide,
        array $titles,
        int $order_nr = 0,
        bool $outdated = false
    ) {
        $this->type = $type;
        $this->characteristic = $characteristic;
        $this->hide = $hide;
        $this->titles = $titles;
        $this->order_nr = $order_nr;
        $this->outdated = $outdated;
    }

    /**
     * With style id
     * @param int $style_id
     * @return Characteristic
     */
    public function withStyleId(int $style_id) : Characteristic
    {
        $clone = clone $this;
        $clone->style_id = $style_id;
        return $clone;
    }

    /**
     * Get style id
     * @return int
     */
    public function getStyleId() : int
    {
        return $this->style_id;
    }

    /**
     * Get characteristic (class name)
     * @return string
     */
    public function getCharacteristic() : string
    {
        return $this->characteristic;
    }

    /**
     * Get type
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Is char hidden?
     * @return bool
     */
    public function isHidden() : bool
    {
        return $this->hide;
    }

    /**
     * Get titles
     * @return array
     */
    public function getTitles() : array
    {
        return $this->titles;
    }

    /**
     * Get order nr
     * @return int
     */
    public function getOrderNr() : int
    {
        return $this->order_nr;
    }

    /**
     * Is char outdated?
     * @return bool
     */
    public function isOutdated() : bool
    {
        return $this->outdated;
    }
}
