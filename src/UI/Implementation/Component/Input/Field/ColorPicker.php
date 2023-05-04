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

use ILIAS\Data;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use Closure;

/**
 * @author Patrick Bechtold <patrick.bechtold@kroepelin-projekte.de>
 */
class ColorPicker extends Input implements C\Input\Field\ColorPicker
{
    /**
     * Input constructor.
     *
     * @param Data\Factory $data_factory
     * @param Factory $refinery
     * @param $label
     * @param $byline
     */
    public function __construct(
        Data\Factory $datafactory,
        Refinery $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct(
            $datafactory,
            $refinery,
            $label,
            $byline
        );
        $trafo = $this->refinery->to()->data('color');
        $this->setAdditionalTransformation($trafo);
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        return is_string($value);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->string()->hasMinLength(4);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return static function () {
        };
    }
}
