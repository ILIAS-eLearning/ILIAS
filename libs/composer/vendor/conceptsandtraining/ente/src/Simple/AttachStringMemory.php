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

use CaT\Ente\Entity as IEntity;

/**
 * In memory implementation of AttachString.
 */
class AttachStringMemory implements AttachString {
    /**
     * @var IEntity
     */
    private $entity;

    /**
     * @var string
     */
    private $attached_string;

    public function __construct(IEntity $entity, $attached_string) {
        assert('is_string($attached_string)');
        $this->entity = $entity;
        $this->attached_string = $attached_string;
    }

    /**
     * @inheritdocs
     */
    public function entity() {
        return $this->entity;
    }

    /**
     * @inheritdocs
     */
    public function attachedString() {
        return $this->attached_string;
    }
}
