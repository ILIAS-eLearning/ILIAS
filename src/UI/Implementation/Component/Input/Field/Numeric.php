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
        $this->setAdditionalTransformation($this->refinery->numeric()->isNumeric());
    }


    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value)
    {
        return is_numeric($value) || $value === "";
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
