<?php

declare(strict_types=1);

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
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use DateTimeImmutable;
use ILIAS\Refinery\Custom\Transformation;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the date input.
 */
class DateTime extends Input implements C\Input\Field\DateTime
{
    use ComponentHelper;
    use JavaScriptBindable;

    public const TIME_FORMAT = 'HH:mm';

    protected DateFormat $format;
    protected ?DateTimeImmutable $min_date = null;
    protected ?DateTimeImmutable $max_date = null;
    protected bool $with_time = false;
    protected bool $with_time_only = false;
    protected ?string $timezone = null;

    /**
     * @var array<string,mixed>
     */
    protected array $additional_picker_config = [];

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);

        $this->format = $data_factory->dateFormat()->standard();

        $datetime_trafo = $refinery->to()->dateTime();
        $trafo = $this->getOptionalNullTransformation($datetime_trafo);
        $this->setAdditionalTransformation($trafo);
    }

    protected function getOptionalNullTransformation(\ILIAS\Refinery\Transformation $or_trafo): Transformation
    {
        return $this->refinery->custom()->transformation(
            function ($v) use ($or_trafo) {
                if (!$v) {
                    return null;
                }
                return $or_trafo->transform($v);
            }
        );
    }

    /**
     * @inheritdoc
     *
     * Allows to pass a \DateTimeImmutable for consistencies sake.
     */
    public function withValue($value)
    {
        // TODO: It would be a lot nicer if the value would be held as DateTimeImmutable
        // internally, but currently this is just to much. Added to the roadmap.
        if ($value instanceof \DateTimeImmutable) {
            $value = $this->format->applyTo($value);
        }

        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    public function withFormat(DateFormat $format): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->format = $format;
        return $clone;
    }

    public function getFormat(): DateFormat
    {
        return $this->format;
    }


    public function withTimezone(string $tz): C\Input\Field\DateTime
    {
        $timezone_trafo = $this->refinery->dateTime()->changeTimezone($tz);
        $clone = clone $this;
        $clone->timezone = $tz;

        $trafo = $this->getOptionalNullTransformation($timezone_trafo);
        /**
         * @var $clone C\Input\Field\DateTime
         */
        $clone = $clone->withAdditionalTransformation($trafo);
        return $clone;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function withMinValue(DateTimeImmutable $datetime): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->min_date = $datetime;
        return $clone;
    }

    public function getMinValue(): ?DateTimeImmutable
    {
        return $this->min_date;
    }

    public function withMaxValue(DateTimeImmutable $datetime): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->max_date = $datetime;
        return $clone;
    }

    public function getMaxValue(): ?DateTimeImmutable
    {
        return $this->max_date;
    }

    public function withUseTime(bool $with_time): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->with_time = $with_time;
        return $clone;
    }

    public function getUseTime(): bool
    {
        return $this->with_time;
    }

    public function withTimeOnly(bool $time_only): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->with_time_only = $time_only;
        return $clone;
    }

    public function getTimeOnly(): bool
    {
        return $this->with_time_only;
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_string($value);
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->refinery->string()->hasMinLength(1)
            ->withProblemBuilder(fn ($txt, $value) => $txt("datetime_required"));
    }

    /**
     * Get config to be passed to the bootstrap picker.
     * @return array <string => mixed>
     */
    public function getAdditionalPickerconfig(): array
    {
        return $this->additional_picker_config;
    }

    /**
     * The bootstrap picker can be configured, e.g. with a minimum date.
     * @param array <string => mixed> $config
     */
    public function withAdditionalPickerconfig(array $config): C\Input\Field\DateTime
    {
        $clone = clone $this;
        $clone->additional_picker_config = array_merge($clone->additional_picker_config, $config);
        return $clone;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return fn ($id) => "$('#$id').on('input dp.change', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').find('input').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').find('input').val());";
    }
}
