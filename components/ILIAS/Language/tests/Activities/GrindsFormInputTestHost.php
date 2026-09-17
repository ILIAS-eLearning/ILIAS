<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Data\Result;
use ILIAS\Language\Activities\GrindsFormInput;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * Minimal host for GrindsFormInput: only mixes in the trait and exposes its
 * private grind() through a public wrapper, so the trait's own contract can
 * be tested in isolation from any concrete Activity - see GrindsFormInputTest.
 */
final class GrindsFormInputTestHost
{
    use GrindsFormInput;

    public function callGrind(FormInput $description, array $raw_parameters): Result
    {
        return $this->grind($description, $raw_parameters);
    }
}
