<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\Data\Result;

/**
 * Describes the interface of inputs that is used for internal
 * processing of data from the client.
 */
interface InputInternal
{

    /**
     * The name of the input as used in HTML.
     *
     * @return string
     */
    public function getName();


    /**
     * Get an input like this one, with a different name.
     *
     * @param    NameSource $source
     *
     * @return    Input
     */
    public function withNameFrom(NameSource $source);


    /**
     * Get an input like this with input from post data.
     *
     * @param    PostData $input
     *
     * @return    Input
     */
    public function withInput(PostData $input);


    /**
     * Get the current content of the input.
     *
     * @return    Result
     */
    public function getContent();
}
