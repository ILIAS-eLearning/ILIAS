<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

namespace CaT\Ente;

/**
 * Helpers to implement providers.
 */
trait ProviderHelper {
    /**
     * Get the component types implemented by the given component.
     *
     * Traverses all implemented interfaces and checks if they extend `Component`.
     *
     * @param   Component $component
     * @return  string[]
     */
    public function componentTypesOf(Component $component) {
        $ret = [];
        foreach (class_implements(get_class($component)) as $interface) {
            if (is_subclass_of($interface, Component::class)) {
                $ret[] = $interface;
            }
        }
        return $ret;
    } 
}
