<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationObject extends ilVerificationObject
{
    public function __construct(string $type, int $a_id = 0, bool $a_reference = true)
    {
        $this->type = $type;

        parent::__construct($a_id, $a_reference);
    }

    /**
     * @return array
     */
    protected function initType()
    {
    }

    /**
     * Return property map (name => type)
     *
     * @return array
     */
    protected function getPropertyMap()
    {
        return array(
            "issued_on" => self::TYPE_DATE,
            "file" => self::TYPE_STRING
        );
    }
}
