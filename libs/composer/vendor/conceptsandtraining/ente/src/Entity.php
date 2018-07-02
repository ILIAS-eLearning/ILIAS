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
 * An entity is to be thought of as a naked object without any behaviour. 
 *
 * ARCH:
 *  - The entity could have a method to provide components by itself. This would
 *    also imply that there is a central place where all components for an entity
 *    are known, thus couple the entity to all existing components. This dependency
 *    should not be introduced to have an extensible system.
 */
interface Entity {
    /**
     * Some ID for this entity.
     *
     * It needs to be guaranteed that every entity has exactly one unique id.
     * It needs to be guaranteed that the id can be serialised.
     *
     * @return  mixed
     */
    public function id();
}
