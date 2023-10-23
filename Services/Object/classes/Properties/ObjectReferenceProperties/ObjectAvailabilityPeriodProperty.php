<?php

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

declare(strict_types=1);

namespace ILIAS\Object\Properties\ObjectReferenceProperties;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;

/**
 * @author Stephan Kergomard
 */
class ObjectAvailabilityPeriodProperty implements \ilObjectProperty
{
    public function __construct(
        private ?int $object_reference_id = null,
        private ?bool $availability_period_enabled = null,
        private ?\DateTimeImmutable $time_limit_start = null,
        private ?\DateTimeImmutable $time_limit_end = null,
        private ?bool $visible_when_disabled = false
    ) {
    }

    public function getObjectReferenceId(): ?int
    {
        return $this->object_reference_id;
    }

    public function getAvailabilityPeriodEnabled(): bool
    {
        return $this->availability_period_enabled === true;
    }

    public function getAvailabilityPeriodStart(): ?\DateTimeImmutable
    {
        return $this->time_limit_start;
    }

    public function getAvailabilityPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->time_limit_end;
    }

    public function getVisibleWhenDisabled(): bool
    {
        return $this->visible_when_disabled;
    }

    public function objectCurrentlyEnabled(): bool
    {
        if ($this->availability_period_enabled === false) {
            return true;
        }

        $timing_start_utc = $this->timing_start->setTimezone(new \DateTimeZone('UTC'));
        $timing_end_utc = $this->timing_end->setTimezone(new \DateTimeZone('UTC'));
        $now_utc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        if ($timing_start_utc < $now_utc && $now_utc < $timing_end_utc) {
            return true;
        }

        return false;
    }

    public function withObjectReferenceId(int $object_reference_id): self
    {
        $clone = clone $this;
        $clone->object_reference_id = $object_reference_id;
        return $clone;
    }

    public function toForm(
        \ilLanguage $language,
        FieldFactory $field_factory,
        Refinery $refinery,
        array $environment = null
    ): FormInput {
        $constraint = $this->getConstraintForActivationLimitedOptionalGroup(
            $refinery,
            $language
        );
        $trafo = $this->getTransformationForActivationLimitedOptionalGroup($refinery);
        $value = $this->getValueForActivationLimitedOptionalGroup(
            new \DateTimeZone($environment['user_time_zone'])
        );

        $inputs['time_limit_start'] = $field_factory->dateTime($language->txt('duration_default_label_start'))
            ->withTimezone($environment['user_time_zone'])
            ->withFormat($environment['user_date_format'])
            ->withUseTime(true);
        $inputs['time_limit_end'] = $field_factory->dateTime($language->txt('duration_default_label_end'))
            ->withTimezone($environment['user_time_zone'])
            ->withFormat($environment['user_date_format'])
            ->withUseTime(true);
        $inputs['visible_when_disabled'] = $field_factory->checkbox(
            $language->txt('activation_visible_when_disabled'),
            $language->txt('activation_visible_when_disabled_info')
        );

        return $field_factory->optionalGroup(
            $inputs,
            $language->txt('rep_visibility_until')
        )->withAdditionalTransformation($constraint)
            ->withAdditionalTransformation($trafo)
            ->withValue($value);
    }

    private function getTransformationForActivationLimitedOptionalGroup(Refinery $refinery): Transformation
    {
        return $refinery->custom()->transformation(
            function (?array $vs): self {
                if ($vs === null
                    || $vs['time_limit_start'] === null
                        && $vs['time_limit_end'] === null) {
                    return new self($this->getObjectReferenceId());
                }

                return new self(
                    $this->getObjectReferenceId(),
                    true,
                    $vs['time_limit_start']?->setTimezone(new \DateTimeZone('UTC')),
                    $vs['time_limit_end']?->setTimezone(new \DateTimeZone('UTC')),
                    $vs['visible_when_disabled']
                );
            }
        );
    }

    private function getConstraintForActivationLimitedOptionalGroup(
        Refinery $refinery,
        \ilLanguage $language
    ): Constraint {
        return $refinery->custom()->constraint(
            function (?array $vs): bool {
                if ($vs === null
                    || $vs['time_limit_start'] === null
                    || $vs['time_limit_end'] === null) {
                    return true;
                }

                if ($vs['time_limit_start'] > $vs['time_limit_end']) {
                    return false;
                }

                return true;
            },
            $language->txt('duration_end_must_not_be_earlier_than_start')
        );
    }


    private function getValueForActivationLimitedOptionalGroup(\DateTimeZone $timezone): ?array
    {
        $value = null;
        if ($this->getAvailabilityPeriodEnabled()) {
            $value = [
                'time_limit_start' => $this->getAvailabilityPeriodStart()?->setTimezone($timezone)->format('Y-m-d H:i') ?? '',
                'time_limit_end' => $this->getAvailabilityPeriodEnd()?->setTimezone($timezone)->format('Y-m-d H:i') ?? '',
                'visible_when_disabled' => $this->getVisibleWhenDisabled()
            ];
        }
        return $value;
    }
}
