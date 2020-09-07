<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceEntityFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceEntityFactory
{
    /**
     * @param string $name
     * @return \ilTermsOfServiceAcceptanceEntity
     * @throws \InvalidArgumentException
     */
    public function getByName(string $name) : \ilTermsOfServiceAcceptanceEntity
    {
        switch (strtolower($name)) {
            case 'iltermsofserviceacceptanceentity':
                return new \ilTermsOfServiceAcceptanceEntity();

            default:
                throw new \InvalidArgumentException('Entity not supported');
        }
    }
}
