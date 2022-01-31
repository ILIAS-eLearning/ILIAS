<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBag
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryCriteriaBag extends ArrayObject implements ilTermsOfServiceJsonSerializable
{
    /**
     * ilTermsOfServiceAcceptanceHistoryCriteriaBag constructor.
     * @param string|ilTermsOfServiceEvaluableCriterion[] $data
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            $this->ensureValidArrayTypes($data);

            parent::__construct(array_values(array_map(static function (
                ilTermsOfServiceEvaluableCriterion $criterionAssignment
            ) : array {
                return [
                    'id' => $criterionAssignment->getCriterionId(),
                    'value' => $criterionAssignment->getCriterionValue()
                ];
            }, $data)));
        } else {
            parent::__construct([]);

            if (is_string($data)) {
                $this->fromJson($data);
            }
        }
    }

    private function ensureValidArrayTypes(array $data) : void
    {
        array_walk($data, static function ($value) : void {
            if (!($value instanceof ilTermsOfServiceEvaluableCriterion)) {
                throw new ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected instanceof '%s'",
                    var_export($value, true),
                    ilTermsOfServiceEvaluableCriterion::class
                ));
            }
        });
    }

    private function ensureValidInternalTypes(array $data) : void
    {
        array_walk($data, static function ($value) : void {
            if (!is_array($value)) {
                throw new ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    'Unexpected element found, given %s, expected array',
                    var_export($value, true)
                ));
            }

            if (!array_key_exists('id', $value) || !array_key_exists('value', $value) || count($value) !== 2) {
                throw new ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected array with keys 'id' and 'value'",
                    var_export($value, true)
                ));
            }
            
            $x = 5;
        });
    }

    public function toJson() : string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritdoc
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function fromJson(string $json) : void
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                'Unexpected element found, given %s, expected array',
                var_export($data, true)
            ));
        }

        $this->ensureValidInternalTypes($data);

        $this->exchangeArray($data);
    }

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
