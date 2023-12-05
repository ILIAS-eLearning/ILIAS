<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * This is what a Rating Input looks like.
 */
interface Rating extends FormInput
{
    /**
     * This text is diplayed over the actual input/stars to describe the issue
     * to be rated (as opposed to the input's label, where there is less space).
     */
    public function withAdditionalText(string $text): self;
}
