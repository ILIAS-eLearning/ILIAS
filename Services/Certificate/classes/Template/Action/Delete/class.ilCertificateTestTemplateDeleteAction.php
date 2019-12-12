<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTestTemplateDeleteAction implements ilCertificateDeleteAction
{
    /**
     * @var ilCertificateDeleteAction
     */
    private $deleteAction;

    /**
     * @var ilCertificateObjectHelper
     */
    private $objectHelper;

    public function __construct(
        ilCertificateDeleteAction $deleteAction,
        ilCertificateObjectHelper $objectHelper
    ) {
        $this->deleteAction = $deleteAction;
        $this->objectHelper = $objectHelper;
    }

    /**
     * @param $templateId
     * @param $objectId
     * @return mixed
     */
    public function delete($templateId, $objectId)
    {
        $this->deleteAction->delete($templateId, $objectId);
    }
}
