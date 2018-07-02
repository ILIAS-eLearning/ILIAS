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
 * An shared unbound provider is an unbound provider that has a multiple owners
 * and may provide components based on a combination of owners properties.
 */
abstract class SharedUnboundProvider implements UnboundProvider {
    /**
     * @var \ilObject[]
     */
    private $owners;

    /**
     * @var array<int, int>
     */
    private $ids;

    /**
     * @var string
     */
    private $object_type;

    final public function __construct(array $owners, $object_type) {
        $this->owners = [];
        $this->ids = [];
        foreach($owners as $id => $owner) {
            assert('is_int($id)');
            assert('$owner instanceof \ilObject');
            $this->owners[] = $owner;
            $this->ids[$owner->getId()] = $id;
        }
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
        $id = $owner->getId();
        if (!isset($this->ids[$id])) {
            throw new \InvalidArgumentException(
                "Object with id ".$owner->getId()." is not an owner");
        }
        return $this->ids[$id];
    }

    /**
     * @inheritdocs
     */
    final public function owners() {
        return $this->owners;
    }

    /**
     * @inheritdocs
     */
    final public function objectType() {
        return $this->object_type;
    }
}
