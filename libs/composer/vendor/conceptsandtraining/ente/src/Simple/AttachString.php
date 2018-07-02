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
 * Attaches a string to an entity.
 *
 * Intended to be used for testing.
 */
interface AttachString extends \CaT\Ente\Component {
    /**
     * Get the attached string.
     *
     * @return string 
     */
    public function attachedString();
}
