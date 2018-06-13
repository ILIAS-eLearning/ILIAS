<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\ILIAS;

/**
 * An entity over an ILIAS object.
 */
class Entity implements \CaT\Ente\Entity {
    /**
     * @var \ilObject 
     */
    private $object;

    public function __construct(\ilObject $object) {
        $this->object = $object;
    }

    /**
     * @inheritdocs
     */
    public function id() {
        return $this->object->getId();
    }

    /**
     * @return  \ilObject
     */
    public function object() {
        return $this->object;
    }
}
