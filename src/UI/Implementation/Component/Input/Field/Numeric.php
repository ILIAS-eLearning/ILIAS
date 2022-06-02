<?php declare(strict_types=1);

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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * This implements the numeric input.
 */
class Numeric extends Input implements C\Input\Field\Numeric
{
    private bool $complex = false;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);

        /**
         * @var $trafo_numericOrNull Transformation
         */
        $trafo_numericOrNull = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->int()
        ])
        ->withProblemBuilder(fn ($txt) => $txt("ui_numeric_only"));

        $this->setAdditionalTransformation($trafo_numericOrNull);
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return is_numeric($value) || $value === "" || $value === null;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->numeric()->isNumeric();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : Closure
    {
        return fn ($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    /**
     * @inheritdoc
     */
    public function isComplex() : bool
    {
        return $this->complex;
    }
}
