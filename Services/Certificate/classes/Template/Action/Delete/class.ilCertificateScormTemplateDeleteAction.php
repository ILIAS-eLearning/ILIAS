<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateScormTemplateDeleteAction implements ilCertificateDeleteAction
{
    private ilCertificateTemplateDeleteAction $deleteAction;
    private ilSetting $setting;

    public function __construct(ilCertificateTemplateDeleteAction $deleteAction, ?ilSetting $setting = null)
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
     * @return void
     * @throws ilDatabaseException
     */
    public function delete($templateId, $objectId) : void
    {
        $this->deleteAction->delete($templateId, $objectId);

        $this->setting->delete('certificate_' . $objectId);
    }
}
