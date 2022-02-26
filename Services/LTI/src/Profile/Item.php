<?php

namespace ILIAS\LTI\Profile;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class Item
{

/**
 * ID of item.
 *
 * @var string $id
 */
    public $id = null;
    /**
     * Name of item.
     *
     * @var string $name
     */
    public $name = null;
    /**
     * Description of item.
     *
     * @var string $description
     */
    public $description = null;
    /**
     * URL of item.
     *
     * @var string $url
     */
    public $url = null;
    /**
     * Version of item.
     *
     * @var string $version
     */
    public $version = null;
    /**
     * Timestamp of item.
     *
     * @var int $timestamp
     */
    public $timestamp = null;

    /**
     * Class constructor.
     *
     * @param string $id           ID of item (optional)
     * @param string $name         Name of item (optional)
     * @param string $description  Description of item (optional)
     * @param string $url          URL of item (optional)
     * @param string $version      Version of item (optional)
     * @param int    $timestamp    Timestamp of item (optional)
     */

    public function __construct($id = null, $name = null, $description = null, $url = null, $version = null, $timestamp = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->version = $version;
        $this->timestamp = $timestamp;
    }
}
