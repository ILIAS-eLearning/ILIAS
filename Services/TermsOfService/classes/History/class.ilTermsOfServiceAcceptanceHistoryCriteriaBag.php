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
            ): array {
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

    private function ensureValidArrayTypes(array $data): void
    {
        array_walk($data, static function ($value): void {
            if (!($value instanceof ilTermsOfServiceEvaluableCriterion)) {
                throw new ilTermsOfServiceUnexpectedCriteriaBagContentException(sprintf(
                    "Unexpected element found, given %s, expected instanceof '%s'",
                    var_export($value, true),
                    ilTermsOfServiceEvaluableCriterion::class
                ));
            }
        });
    }

    private function ensureValidInternalTypes(array $data): void
    {
        array_walk($data, static function ($value): void {
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
        });
    }

    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritdoc
     * @throws ilTermsOfServiceUnexpectedCriteriaBagContentException
     */
    public function fromJson(string $json): void
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

    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
