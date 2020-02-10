<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * This type of input is required by forms.
 */
interface FormInputInternal extends InputInternal, FormInput
{
    /**
     * Get an input like this one, with a different name.
     *
     * @param    NameSource $source
     *
     * @return    Input
     */
    public function withNameFrom(NameSource $source);
}
