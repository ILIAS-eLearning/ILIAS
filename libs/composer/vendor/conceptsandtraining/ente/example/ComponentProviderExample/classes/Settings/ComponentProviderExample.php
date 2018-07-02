<?php

namespace CaT\Plugins\ComponentProviderExample\Settings; 

/**
 * Settings for an ComponentProviderExample.
 */
class ComponentProviderExample {
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string[]
     */
    protected $provided_strings;

    public function __construct($obj_id, array $provided_strings) {
        assert('is_int($obj_id)');
        assert('array_sum(array_map("is_string", $provided_strings)) == count($provided_strings)');
        $this->obj_id = $obj_id;
        $this->provided_strings = $provided_strings;
    }

    /**
     * Get the id of the object this is belonging to.
     *
     * @return  int
     */
    public function objId() {
        return $this->obj_id;
    }

    /**
     * Get the provided strings.
     *
     * @return  string[]
     */
    public function providedStrings() {
        return $this->provided_strings; 
    }

    /**
     * Change the provided strings.
     *
     * @param   string[]    $provided_strings
     * @return  ComponentProviderExample
     */
    public function withProvidedStrings(array $provided_strings) {
        assert('array_sum(array_map("is_string", $provided_strings)) == count($provided_strings)');
        $clone = clone $this;
        $clone->provided_strings = $provided_strings;
        return $clone;
    }
}
