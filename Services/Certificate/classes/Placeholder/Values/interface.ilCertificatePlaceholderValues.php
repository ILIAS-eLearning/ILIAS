<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificatePlaceholderValues
{
    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     *
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     *
     * @param int $userId
     * @param int $objId
     * @throws ilInvalidCertificateException
     * @return mixed - [PLACEHOLDER] => 'actual value'
     */
    public function getPlaceholderValues(int $userId, int $objId);

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     *
     * @param int $userId
     * @param int $objId
     * @return array
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId);
}
