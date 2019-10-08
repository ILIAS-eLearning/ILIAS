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
    protected function isClientSideValueOk($value)
    {
        $ok = is_array($value) || is_null($value);
        return $ok;
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
				var options = {};
				var options_combined = [];
				$('#$id').find('input').each(function() {
					options[$(this).val()] = $(this).prop('checked').toString();
				});
				for (let [key, value] of Object.entries(options)) {
					options_combined.push(key + ': ' + value);
				}
				return options_combined.join(', ');
			}
			$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			});
			il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			";
            return $code;
        };
    }
}
