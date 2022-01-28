<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Data\DataSize;

/**
 * Content style data object factory
 * @author Alexander Killing <killing@leifos.de>
 */
class DataFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Characteristic
     * @param string $type
     * @param string $characteristic
     * @param bool   $hide
     * @param array  $titles
     * @param int    $style_id
     * @param int    $order_nr
     * @param bool   $outdated
     * @return Characteristic
     */
    public function characteristic(
        string $type,
        string $characteristic,
        bool $hide,
        array $titles,
        int $style_id = 0,
        int $order_nr = 0,
        bool $outdated = false
    ) : Characteristic {
        $c = new Characteristic(
            $type,
            $characteristic,
            $hide,
            $titles,
            $order_nr,
            $outdated
        );
        if ($style_id > 0) {
            $c = $c->withStyleId($style_id);
        }
        return $c;
    }

    // image
    public function image(
        string $path,
        DataSize $size,
        int $width,
        int $height
    ) : Image {
        return new Image(
            $path,
            $size,
            $width,
            $height
        );
    }
}
