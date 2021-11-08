<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificateFormRepository
{
    public function createForm(ilCertificateGUI $certificateGUI) : ilPropertyFormGUI;

    public function save(array $formFields) : void;

    public function fetchFormFieldData(string $content) : array;
}
