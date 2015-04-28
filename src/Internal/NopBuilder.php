<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\Internal\HTML as H;

/* A builder that produces a completely empty piece of HTML. */
class NopBuilder extends Builder {
    public function buildWithDict(RenderDict $dict) {
        return H::nop();
    }
}
   

