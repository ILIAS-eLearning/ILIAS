<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getName(): ?string;

    /**
     * Get an input like this with input from post data.
     *
     * @return static
     */
    public function withInput(InputData $input);

    /**
     * Get the current content of the input.
     */
    public function getContent(): Result;
}
