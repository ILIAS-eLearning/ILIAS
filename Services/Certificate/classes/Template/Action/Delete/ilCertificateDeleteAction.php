<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificateDeleteAction
{
    /**
     * @param $templateId
     * @param $objectId
     * @return mixed
     */
    public function delete($templateId, $objectId);
}
