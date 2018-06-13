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
 * A component attaches data to an entity that enables a certain behaviour for
 * the entity.
 *
 * Components should not provide said behaviour by themselves but present data
 * to a System that processes the Components in an appropriate way.
 *
 * Concrete Components must be abstracted as another interface for this
 * machinery to work correctly.
 */
interface Component {
    /**
     * Get the entity this component is attached to.
     *
     * @return Entity
     */
    public function entity();
}
