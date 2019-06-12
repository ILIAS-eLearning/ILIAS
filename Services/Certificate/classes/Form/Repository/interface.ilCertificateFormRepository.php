<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificateFormRepository
{
    /**
     * @param ilCertificateGUI $certificateGUI
     * @param ilCertificate $certificateObject
     * @return ilPropertyFormGUI
     */
    public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject);

    /**
     * @param array $formFields
     * @return mixed
     */
    public function save(array $formFields);

    /**
     * @param $content
     * @return mixed
     */
    public function fetchFormFieldData(string $content);
}
