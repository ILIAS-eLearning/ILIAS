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
 */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Implementation\Component\Input\InputInternal;

/**
 * This interface describes how form inputs are handled internally.
 * It mostly exists due to PHPUnit tests which need to mock them.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface FormInputInternal extends InputInternal, FormInput
{
}
