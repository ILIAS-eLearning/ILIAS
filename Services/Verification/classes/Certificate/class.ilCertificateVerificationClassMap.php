<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationClassMap
{
    private $map = array(
        'crs'  => 'crsv',
        'tst'  => 'tstv',
        'exc'  => 'excv',
        'sahs' => 'scov'
    );

    /**
     * @param string $type
     * @return string
     * @throws ilException
     */
    public function getVerificationTypeByType(string $type) : string
    {
        if (false === $this->typeExistsInMap($type)) {
            throw new ilException('The given type ' . $type . 'is not mapped as a verification type on the class map');
        }

        return $this->map[$type];
    }

    /**
     * @param string $type
     * @return bool
     */
    private function typeExistsInMap(string $type) : bool
    {
        return array_key_exists($type, $this->map);
    }
}
