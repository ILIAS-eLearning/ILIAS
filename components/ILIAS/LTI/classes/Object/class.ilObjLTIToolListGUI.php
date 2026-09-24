<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Presentation of an LTI object in the listing of its parent container.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilObjLTIToolListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->type = 'lti';
        $this->gui_class_name = ilObjLTIToolGUI::class;
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = false;
        $this->link_enabled = true;
        $this->subscribe_enabled = false;
        $this->progress_enabled = true;
        $this->notice_properties_enabled = true;
        $this->info_screen_enabled = true;
        $this->commands = ilObjLTIToolAccess::_getCommands();
    }

    /**
     * Besides the common properties the type, and the certificate once the user has one.
     *
     * @throws ilCtrlException
     */
    public function getProperties(): array
    {
        global $DIC;

        $properties = parent::getProperties();
        $properties[] = [
            'alert' => false,
            'property' => $this->lng->txt('type'),
            'value' => $this->lng->txt('obj_lti'),
        ];

        if (new ilCertificateDownloadValidator()->isCertificateDownloadable($this->user->getId(), $this->obj_id)) {
            $this->lng->loadLanguageModule('certificate');
            $this->ctrl->setParameterByClass(ilObjLTIToolGUI::class, 'ref_id', $this->ref_id);
            $properties[] = [
                'alert' => false,
                'property' => $this->lng->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render($DIC->ui()->factory()->link()->standard(
                    $this->lng->txt('download_certificate'),
                    $this->ctrl->getLinkTargetByClass(
                        [ilRepositoryGUI::class, ilObjLTIToolGUI::class],
                        ilObjLTIToolGUI::CMD_DELIVER_CERTIFICATE
                    )
                )),
            ];
        }

        return $properties;
    }
}
