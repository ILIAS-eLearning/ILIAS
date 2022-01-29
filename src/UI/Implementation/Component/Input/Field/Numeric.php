<?php declare(strict_types=1);

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

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
        ->withProblemBuilder(function ($txt) {
            return $txt("ui_numeric_only");
        });

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
        return function ($id) {
            return "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
        };
    }

    /**
     * @inheritdoc
     */
    public function isComplex() : bool
    {
        return $this->complex;
    }
}
