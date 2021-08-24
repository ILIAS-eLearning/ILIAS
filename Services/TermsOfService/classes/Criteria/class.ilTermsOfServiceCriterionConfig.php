<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function toJson() : string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json) : void
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->exchangeArray($data);
    }

    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }
}
