<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Factory as ValidationFactory;

/**
 * This implements the text input.
 */
class Text extends Input implements C\Input\Field\Text
{
    /**
     * @inheritdoc
     */
    public function __construct(
        DataFactory $data_factory,
        ValidationFactory $validation_factory,
        TransformationFactory $transformation_factory,
        $label,
        $byline
    ) {
        parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
        $this->setAdditionalTransformation($transformation_factory->custom(function ($v) {
            return strip_tags($v);
        }));
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value)
    {
        return is_string($value);
    }


    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        return $this->validation_factory->hasMinLength(1);
    }
}
