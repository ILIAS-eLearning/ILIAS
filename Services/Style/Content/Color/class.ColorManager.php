<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use \ILIAS\Style\Content\Access;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ColorManager
{
    /**
     * @var CharacteristicDBRepo
     */
    protected $characteristic_repo;

    /**
     * @var ColorDBRepo
     */
    protected $color_repo;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var Access\StyleAccessManager
     */
    protected $access_manager;

    /**
     * @var int
     */
    protected $style_id;

    /**
     * Constructor
     */
    public function __construct(
        int $style_id,
        Access\StyleAccessManager $access_manager,
        CharacteristicDBRepo $char_repo,
        ColorDBRepo $color_repo
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->user = $DIC->user();
        $this->characteristic_repo = $char_repo;
        $this->color_repo = $color_repo;
        $this->access_manager = $access_manager;
        $this->style_id = $style_id;
    }

    /**
     * Add color
     * @param string $a_name
     * @param string $a_code
     */
    public function addColor(
        string $a_name,
        string $a_code
    ) : void {
        $this->color_repo->addColor(
            $this->style_id,
            $a_name,
            $a_code
        );
    }

    /**
     * Check whether color exists
     * @param string $name
     * @return bool
     */
    public function colorExists(
        string $name
    ) : bool {
        return $this->color_repo->colorExists(
            $this->style_id,
            $name
        );
    }

    /**
     * Update color
     * @param string $name
     * @param string $new_name
     * @param string $code
     * @throws ContentStyleNoPermissionException
     */
    public function updateColor(
        string $name,
        string $new_name,
        string $code
    ) {
        if (!$this->access_manager->checkWrite()) {
            throw new ContentStyleNoPermissionException("No write permission for style.");
        }

        $this->color_repo->updateColor(
            $this->style_id,
            $name,
            $new_name,
            $code
        );

        \ilObjStyleSheet::_writeUpToDate($this->style_id, false);

        // rename also the name in the style parameter values
        if ($name != $new_name) {
            $this->characteristic_repo->updateColorName(
                $this->style_id,
                $name,
                $new_name
            );
        }
    }
}
