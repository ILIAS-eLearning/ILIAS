<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\Data\Result;

/**
 * Describes the interface of inputs that is used for internal
 * processing of data from the client.
 */
interface InputInternal extends Input
{
    /**
     * The name of the input as used in HTML.
     */
    public function getName() : ?string;

    /**
     * Get an input like this with input from post data.
     *
     * @return static
     */
    public function withInput(InputData $input);

    /**
     * Get the current content of the input.
     */
    public function getContent() : Result;
}
