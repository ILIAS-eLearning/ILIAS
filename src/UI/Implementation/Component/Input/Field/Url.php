<?php declare(strict_types=1);


/* Copyright (c) 2021 Luka Stocker <luka.stocker@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;

/**
 * This implements the URL input.
 */
class Url extends Input implements C\Input\Field\Url
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @inheritdoc
     */
    public function __construct(
        DataFactory $data_factory,
        Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->addValidation();
        $this->addTransformation();
    }

    protected function addValidation()
    {
        $txt_id = 'ui_invalid_url';
        $error = function (callable $txt, $value) use ($txt_id) {
            return $txt($txt_id, $value);
        };
        $is_ok = function ($v) {
            if (is_string($v) && trim($v) === '') {
                return true;
            }
            try {
                $this->data_factory->uri($v);
            } catch (\Throwable $e) {
                return false;
            }
            return true;
        };

        $from_before_until = $this->refinery->custom()->constraint($is_ok, $error);
        $this->setAdditionalTransformation($from_before_until);
    }

    protected function addTransformation()
    {
        $trafo = $this->refinery->custom()->transformation(function ($v) {
            if (is_string($v) && trim($v) === '') {
                return null;
            }
            return $this->data_factory->uri($v);
        });

        $this->setAdditionalTransformation($trafo);
    }

    /**
     * @inheritcoc
     */
    public static function getURIChecker() : \Closure
    {
        return static function (string $value) : bool {
            try {
                new URI($value);
            } catch (\Throwable $e) {
                return false;
            }
            return true;
        };
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (is_string($value) && trim($value) === "") {
            return true;
        }

        if (!self::getURIChecker()) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        if (!self::getURIChecker()) {
            return false;
        }
        return true;
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
