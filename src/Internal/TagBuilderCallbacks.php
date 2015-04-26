<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/**
 * Interface to be implemented by classes that should be used by TagBuilder. 
 */
interface TagBuilderCallbacks {
    /**
     * Get the attributes for the tag to be build. Should return a dict of
     * string => string.
     */
    public function getAttributes(RenderDict $dict, $name);

    /**
     * Get the content of the new tag. Should return an HTML or null.
     */
    public function getContent(RenderDict $dict, $name);
}
    
?>
