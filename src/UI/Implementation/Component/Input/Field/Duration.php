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
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Refinery as Refinery;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use DateTimeImmutable;
use Closure;
use ilLanguage;

/**
 * This implements the duration input group.
 */
class Duration extends Group implements C\Input\Field\Duration
{
    use ComponentHelper;
    use JavaScriptBindable;

    protected DateFormat $format;
    protected DateTimeImmutable $min_date;
    protected DateTimeImmutable $max_date;
    protected bool $with_time = false;
    protected bool $with_time_only = false;
    protected ?string $timezone = null;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng,
        Factory $field_factory,
        string $label,
        ?string $byline
    ) {
        $inputs = [
            $field_factory->dateTime($lng->txt('duration_default_label_start')),
            $field_factory->dateTime($lng->txt('duration_default_label_end'))
        ];

        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);

        $this->addTransformation();
        $this->addValidation();
    }

    /**
     * Return-value of Duration is an assoc array with start, end and interval.
     * If one or the other of start/end is omitted, there is no possible calculation
     * of a duration - in this case, null is being returned.
     *
     */
    protected function addTransformation() : void
    {
        $duration = $this->refinery->custom()->transformation(function ($v) : ?array {
            list($from, $until) = $v;
            if ($from && $until) {
                return ['start' => $from, 'end' => $until, 'interval' => $from->diff($until)];
            }
            return null;
        });
        $this->setAdditionalTransformation($duration);
    }

    /**
     * Input is valid, if start is before end.
     */
    protected function addValidation() : void
    {
        $txt_id = 'duration_end_must_not_be_earlier_than_start';
        $error = fn (callable $txt, $value) => $txt($txt_id, $value);
        $is_ok = function ($v) {
            if (is_null($v)) {
                return true;
            }
            return $v['start'] < $v['end'];
        };

        $from_before_until = $this->refinery->custom()->constraint($is_ok, $error);
        $this->setAdditionalTransformation($from_before_until);
    }

    /**
     * @inheritdoc
     */
    public function withFormat(DateFormat $format) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->format = $format;
        $clone->applyFormat();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFormat() : DateFormat
    {
        return $this->format;
    }

    /**
     * apply format to inputs
     */
    protected function applyFormat() : void
    {
        $this->inputs = array_map(
            fn ($input) => $input->withFormat($this->getFormat()),
            $this->inputs
        );
    }

    /**
     * @inheritdoc
     */
    public function withMinValue(DateTimeImmutable $date) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->min_date = $date;
        $clone->applyMinValue();
        return $clone;
    }

    /**
     * apply format to inputs
     */
    protected function applyMinValue() : void
    {
        $this->inputs = array_map(
            fn ($input) => $input->withMinValue($this->getMinValue()),
            $this->inputs
        );
    }

    /**
     * @inheritdoc
     */
    public function getMinValue() : ?DateTimeImmutable
    {
        return $this->min_date;
    }

    /**
     * @inheritdoc
     */
    public function withMaxValue(DateTimeImmutable $date) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->max_date = $date;
        $clone->applyMaxValue();
        return $clone;
    }

    /**
     * apply format to inputs
     */
    protected function applyMaxValue() : void
    {
        $this->inputs = array_map(
            fn ($inpt) => $inpt->withMaxValue($this->getMaxValue()),
            $this->inputs
        );
    }

    /**
     * @inheritdoc
     */
    public function getMaxValue() : ?DateTimeImmutable
    {
        return $this->max_date;
    }

    /**
     * @inheritdoc
     */
    public function withTimeOnly(bool $time_only) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->with_time_only = $time_only;
        $clone->applyWithTimeOnly();
        return $clone;
    }

    /**
     * apply format to inputs
     */
    protected function applyWithTimeOnly() : void
    {
        $this->inputs = array_map(
            fn ($input) => $input->withTimeOnly($this->getTimeOnly()),
            $this->inputs
        );
    }

    /**
     * @inheritdoc
     */
    public function getTimeOnly() : bool
    {
        return $this->with_time_only;
    }

    /**
     * @inheritdoc
     */
    public function withUseTime(bool $with_time) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->with_time = $with_time;
        $clone->applyWithUseTime();
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUseTime() : bool
    {
        return $this->with_time;
    }

    /**
     * apply format to inputs
     */
    protected function applyWithUseTime() : void
    {
        $this->inputs = array_map(
            fn ($input) => $input->withUseTime($this->getUseTime()),
            $this->inputs
        );
    }

    /**
     * @inheritdoc
     */
    public function withTimezone(string $tz) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->timezone = $tz;
        $clone->inputs = array_map(
            fn ($input) => $input->withTimezone($tz),
            $clone->inputs
        );
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTimezone() : ?string
    {
        return $this->timezone;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Refinery\Custom\Constraint
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : Closure
    {
        return fn ($id) => "var combinedDuration = function() {
				var options = [];
				$('#$id').find('input').each(function() {
					options.push($(this).val());
				});
				return options.join(' - ');
			}
			$('#$id').on('input dp.change', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', combinedDuration());
			});
			il.UI.input.onFieldUpdate(event, '$id', combinedDuration());";
    }

    public function withLabels(string $start_label, string $end_label) : C\Input\Field\Duration
    {
        $clone = clone $this;
        $clone->inputs = [
            $clone->inputs[0]->withLabel($start_label),
            $clone->inputs[1]->withLabel($end_label)
        ];
        return $clone;
    }
}
