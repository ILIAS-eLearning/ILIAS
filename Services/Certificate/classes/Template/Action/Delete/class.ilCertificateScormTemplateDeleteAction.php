<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormTemplateDeleteAction implements ilCertificateDeleteAction
{
    /**
     * @var ilCertificateTemplateDeleteAction
     */
    private $deleteAction;

    /**
     * @var ilSetting|null
     */
    private $setting;

    /**
     * @param ilCertificateTemplateDeleteAction $deleteAction
     * @param ilSetting|null $setting
     */
    public function __construct(ilCertificateTemplateDeleteAction $deleteAction, ilSetting $setting = null)
    {
        $this->deleteAction = $deleteAction;

        if (null === $setting) {
            $setting = new ilSetting('scorm');
        }
        $this->setting = $setting;
    }

    /**
     * @param $templateId
     * @param $objectId
     * @return mixed
     * @throws ilDatabaseException
     */
    public function delete($templateId, $objectId)
    {
        $this->deleteAction->delete($templateId, $objectId);

        $this->setting->delete('certificate_' . $objectId);
    }
}
