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
 * An separated unbound provider is a unbound provider that has a single owner.
 */
abstract class SeparatedUnboundProvider implements UnboundProvider {
    /**
     * @var int
     */
    private $id;

    /**
     * @var \ilObject
     */
    private $owner;

    /**
     * @var string
     */
    private $object_type;

    final public function __construct($id, \ilObject $owner, $object_type) {
        assert('is_int($id)');
        $this->id = $id;
        $this->owner = $owner;
        assert('is_string($object_type)');
        $this->object_type = $object_type;
    }

    /**
     * @inheritdocs
     */
    abstract public function componentTypes();

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    abstract public function buildComponentsOf($component_type, Entity $entity);

    /**
     * @inheritdocs
     */
    final public function idFor(\ilObject $owner) {
        if ($owner->getId() !== $this->owner->getId()) {
            throw new \InvalidArgumentException(
                "Object with id ".$owner->getId()." is not the owner with id ".$this->owner->getId());
        }
        return $this->id;
    }

    /**
     * @inheritdocs
     */
    final public function owners() {
        return [$this->owner];
    }

    /**
     * @inheritdocs
     */
    final public function owner() {
        return $this->owner;
    }

    /**
     * @inheritdocs
     */
    final public function objectType() {
        return $this->object_type;
    }
}
