<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestTemplateDeleteAction implements ilCertificateDeleteAction
{
    private ilCertificateDeleteAction $deleteAction;
    private ilCertificateObjectHelper $objectHelper;

    public function __construct(
        ilCertificateDeleteAction $deleteAction,
        ilCertificateObjectHelper $objectHelper
    ) {
        $this->deleteAction = $deleteAction;
        $this->objectHelper = $objectHelper;
    }

    public function delete($templateId, $objectId) : void
    {
        $this->deleteAction->delete($templateId, $objectId);
    }
}
