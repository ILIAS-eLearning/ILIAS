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
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the textarea input.
 */
class Textarea extends Input implements C\Input\Field\Textarea
{
    use JavaScriptBindable;
    
    protected ?int $max_limit = null;
    
    protected ?int $min_limit = null;

    /**
     * @inheritdoc
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->setAdditionalTransformation(
            $refinery->string()->stripTags()
        );
    }

    /**
     * set maximum number of characters
     */
    public function withMaxLimit(int $max_limit) : C\Input\Field\Textarea
    {
        /**
         * @var $clone Textarea
         */
        $clone = $this->withAdditionalTransformation(
            $this->refinery->string()->hasMaxLength($max_limit)
        );
        $clone->max_limit = $max_limit;
        return $clone;
    }

    /**
     * get maximum limit of characters
     * @return mixed
     */
    public function getMaxLimit() : ?int
    {
        return $this->max_limit;
    }

    /**
     * set minimum number of characters
     */
    public function withMinLimit(int $min_limit) : C\Input\Field\Textarea
    {
        /**
         * @var $clone Textarea
         */
        $clone = $this->withAdditionalTransformation(
            $this->refinery->string()->hasMinLength($min_limit)
        );
        $clone->min_limit = $min_limit;
        return $clone;
    }

    /**
     * get minimum limit of characters
     * @return mixed
     */
    public function getMinLimit() : ?int
    {
        return $this->min_limit;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return is_string($value);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
    {
        if ($this->min_limit) {
            return $this->refinery->string()->hasMinLength($this->min_limit);
        }
        return $this->refinery->string()->hasMinLength(1);
    }

    /**
     * @inheritdoc
     */
    public function isLimited() : bool
    {
        return $this->min_limit > 0 || $this->max_limit > 0;
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
