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

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Constraint;
use Closure;
use Throwable;

/**
 * This implements the URL input.
 */
class Url extends Input implements C\Input\Field\Url
{
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

    protected function addValidation() : void
    {
        $txt_id = 'ui_invalid_url';
        $error = fn (callable $txt, $value) => $txt($txt_id, $value);
        $is_ok = function ($v) {
            if (is_string($v) && trim($v) === '') {
                return true;
            }
            try {
                $this->data_factory->uri($v);
            } catch (Throwable $e) {
                return false;
            }
            return true;
        };

        $from_before_until = $this->refinery->custom()->constraint($is_ok, $error);
        $this->setAdditionalTransformation($from_before_until);
    }

    protected function addTransformation() : void
    {
        $trafo = $this->refinery->custom()->transformation(function ($v) : ?\ILIAS\Data\URI {
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
    public static function getURIChecker() : Closure
    {
        return static function (string $value) : bool {
            try {
                new URI($value);
            } catch (Throwable $e) {
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
    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->custom()->constraint(self::getURIChecker(), 'Not an URI');
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
}
