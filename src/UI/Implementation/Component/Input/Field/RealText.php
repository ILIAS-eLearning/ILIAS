<?php

/* Copyright (c) 2021 Adrian Lüthi <adi.l@bluewin.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;

/**
 * This implements the realtext input.
 */
class RealText extends Input implements C\Input\Field\RealText
{
    /**
     * @inheritdoc
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        $label,
        $byline
        ) {
            parent::__construct($data_factory, $refinery, $label, $byline);

            $this->setAdditionalTransformation(
                $refinery->string()->stripTags()
            );

            $this->on_load_code_binder = function($id) {
                return "il.UI.input.realtext.initiateEditor($id);";
            };
    }


    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (! is_string($value)) {
            return false;
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        return $this->refinery->string()->hasMinLength(1);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        // TODO whatever this is
        return function ($id) {
            return "";
            $code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
            return $code;
        };
    }

    /**
     * @inheritdoc
     */
    public function isComplex()
    {
        return false;
    }
}
