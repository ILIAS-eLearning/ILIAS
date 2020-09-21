<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;

/**
 * This implements the numeric input.
 */
class Numeric extends Input implements C\Input\Field\Numeric
{
    /**
     * Numeric constructor.
     *
     * @param DataFactory $data_factory
     * @param ValidationFactory $validation_factory
     * @param \ILIAS\Refinery\Factory $refinery
     * @param             $label
     * @param             $byline
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        $label,
        $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);

        $trafo_empty2null = $this->refinery->custom()->transformation(
            function ($v) {
                if (trim($v) === '') {
                    return null;
                }
                return $v;
            },
            ""
        );
        $trafo_numericOrNull = $this->refinery->logical()->logicalOr([
            $this->refinery->numeric()->isNumeric(),
            $this->refinery->null()
        ])
        ->withProblemBuilder(function ($txt, $value) {
            return $txt("ui_numeric_only");
        });

        $this->setAdditionalTransformation($trafo_empty2null);
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
    protected function getConstraintForRequirement()
    {
        return $this->refinery->numeric()->isNumeric();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) {
            $code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
            return $code;
        };
    }
}
