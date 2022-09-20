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
 * Class ilTermsOfServiceCriterionConfig
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionConfig extends ArrayObject implements ilTermsOfServiceJsonSerializable
{
    /**
     * ilTermsOfServiceCriterionConfig constructor.
     * @param string|array $data
     */
    public function __construct($data = [])
    {
        if (is_array($data)) {
            parent::__construct($data);
        } else {
            parent::__construct([]);

            if (is_string($data)) {
                $this->fromJson($data);
            }
        }
    }

    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): void
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->exchangeArray($data);
    }

    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
