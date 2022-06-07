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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the select.
 */
class Select extends Input implements C\Input\Field\Select
{
    protected array $options;
    protected string $label;

    /**
     * @var mixed
     */
    protected $value;
    private bool $complex = false;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        array $options,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->options = $options;
    }

    /**
     * @return array with the key/value options.
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
        return in_array($value, array_keys($this->options)) || $value == "";
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->string()->hasMinLength(1);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : Closure
    {
        return fn ($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id option:selected').text());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id option:selected').text());";
    }

    /**
     * @inheritdoc
     */
    public function isComplex() : bool
    {
        return $this->complex;
    }
}
