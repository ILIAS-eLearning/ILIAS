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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input as CI;
use Closure;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class HiddenList extends HasDynamicInputs implements CI\Field\HiddenList
{
    public function __construct(
        \ILIAS\Language\Language $language,
        \ILIAS\Data\Factory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        CI\Container\Form\FormInput $dynamic_input_template,
    ) {
        parent::__construct(
            $language,
            $data_factory,
            $refinery,
            $dynamic_input_template,
            "",
            null,
        );
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return parent::isClientSideValueOk($value);
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement(): ?\ILIAS\Refinery\Constraint
    {
        return null;
    }
}
