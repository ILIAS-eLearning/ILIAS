<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente\Simple;

/**
 * Simple implementation for an entity.
 */
class Entity implements \CaT\Ente\Entity {
    /**
     * @var integer
     */
    private $id;

    public function __construct($id) {
        assert('is_integer($id)');
        $this->id = $id;
    }

    /**
     * @inheritdocs
     */
    public function id() {
        return $this->id;
    }
}
