<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Signal;

/**
 * This implements the multi-select input.
 */
class MultiSelect extends Input implements C\Input\Field\MultiSelect
{

    /**
     * @var array <string,string> {$value => $label}
     */
    protected $options = [];

    /**
     * @var bool
     */
    private $complex = true;

    /**
     * @param DataFactory $data_factory
     * @param \ILIAS\Refinery\Factory $refinery
     * @param string $label
     * @param array $options
     * @param $byline
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        $label,
        $options,
        $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (is_null($value)) {
            return true;
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!array_key_exists($v, $this->options)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        $constraint = $this->refinery->custom()->constraint(
            function ($value) {
                return (is_array($value) && count($value) > 0);
            },
            "Empty"
        );
        return $constraint;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) {
            $code = "var checkedBoxes = function() {
				var options = [];
				$('#$id').find('li').each(function() {
				    if ($(this).find('input').prop('checked')) {
					    options.push($(this).find('span').text());
                    }
				});
				return options.join(', ');
			}
			$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			});
			il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			";
            return $code;
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
