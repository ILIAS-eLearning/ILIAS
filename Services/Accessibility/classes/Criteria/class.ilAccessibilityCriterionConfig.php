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

/**
 * Class ilAccessibilityCriterionConfig
 */
class ilAccessibilityCriterionConfig extends ArrayObject implements ilAccessibilityJsonSerializable
{
    /**
     * ilAccessibilityCriterionConfig constructor.
     * @param string|array
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
     */
    public function fromJson(string $json) : void
    {
        $data = json_decode($json, true);

        $this->exchangeArray($data);
    }

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
