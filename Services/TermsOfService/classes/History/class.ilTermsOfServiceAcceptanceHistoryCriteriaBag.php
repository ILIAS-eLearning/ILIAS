<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBag
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryCriteriaBag extends \ArrayObject implements \ilTermsOfServiceJsonSerializable
{
    /**
     * ilTermsOfServiceAcceptanceHistoryCriteriaBag constructor.
     * @param string|\ilTermsOfServiceEvaluableCriterion[]
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            $this->ensureValidArrayTypes($data);

            parent::__construct(array_values(array_map(function (\ilTermsOfServiceEvaluableCriterion $criterionAssignment) {
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

    /**
     * @param array $data
     * @throws \ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    private function ensureValidArrayTypes(array $data)
    {
        array_walk($data, function ($value) {
            if (!($value instanceof \ilTermsOfServiceEvaluableCriterion)) {
                throw new \ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected instanceof '%s'",
                    var_export($value, 1),
                    \ilTermsOfServiceEvaluableCriterion::class
                ));
            }
        });
    }

    /**
     * @param array $data
     */
    private function ensureValidInternalTypes(array $data)
    {
        array_walk($data, function ($value) {
            if (!is_array($value)) {
                throw new \ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected array",
                    var_export($value, 1)
                ));
            }

            if (count($value) !== 2 || !array_key_exists('id', $value) || !array_key_exists('value', $value)) {
                throw new \ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected array with keys 'id' and 'value'",
                    var_export($value, 1)
                ));
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function toJson() : string
    {
        $json = json_encode($this);

        return $json;
    }

    /**
     * @inheritdoc
     * @throws \ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function fromJson(string $json)
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                "Unexpected element found, given %s, expected array",
                var_export($data, 1)
            ));
        }

        $this->ensureValidInternalTypes($data);

        $this->exchangeArray($data);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
}
