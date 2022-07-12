<?php declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;

/**
 * Class ilStudyProgrammeTypeFormGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilStudyProgrammeTypeCustomIconsFormGUI extends ilPropertyFormGUI
{
    protected ilStudyProgrammeTypeGUI $parent_gui;
    protected ilStudyProgrammeTypeRepository $type_repo;
    protected Filesystem $webdir;

    public function __construct(
        $parent_gui,
        ilStudyProgrammeTypeRepository $type_repo,
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjUser $user,
        Filesystem $web_dir
    ) {
        $this->parent_gui = $parent_gui;
        $this->type_repo = $type_repo;
        $this->ctrl = $ctrl;
        $this->global_tpl = $tpl;
        $this->lng = $lng;
        $this->user = $user;
        $this->webdir = $web_dir;

        $this->lng->loadLanguageModule('meta');
        $this->initForm();
    }

    /**
     * Save object (create or update)
     */
    public function saveObject(ilStudyProgrammeType $type) : bool
    {
        $type = $this->fillObject($type);
        if (!$type) {
            return false;
        }
        try {
            $this->type_repo->updateType($type);
            $type->updateAssignedStudyProgrammesIcons();
            return true;
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage("failure", $e->getMessage());

            return false;
        }
    }

    public function initForm() : void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle($this->lng->txt('prg_type_custom_icon'));
        $item = new ilImageFileInputGUI($this->lng->txt('icon'), 'icon');
        $item->setSuffixes(array( 'svg' ));
        $item->setInfo($this->lng->txt('prg_type_custom_icon_info'));
        $this->addItem($item);
        $this->addCommandButton('updateCustomIcons', $this->lng->txt('save'));
    }

    /**
     * Add all fields to the form
     */
    public function fillForm(ilStudyProgrammeType $type) : void
    {
        $item = $this->getItemByPostVar('icon');
        if ($type->getIcon() !== '' && $this->webdir->has($type->getIconPath(true))) {
            // TODO: that´s horrible, try to avoid ilUtil in future
            $item->setImage(ilFileUtils::getWebspaceDir() . '/' . $type->getIconPath(true));
        }
    }

    /**
     * Check validity of form and pass values from form to object
     */
    public function fillObject(ilStudyProgrammeType $type) : ?ilStudyProgrammeType
    {
        $this->setValuesByPost();
        if (!$this->checkInput()) {
            return null;
        }
        $file_data = (array) $this->getInput('icon');
        /** @var ilImageFileInputGUI $item */
        $item = $this->getItemByPostVar('icon');
        try {
            if (isset($file_data['name']) && $file_data['name']) {
                $type->removeIconFile();
                $type->setIcon($file_data['name']);
                $type->processAndStoreIconFile($file_data);
            } else {
                if ($item->getDeletionFlag()) {
                    $type->removeIconFile();
                    $type->setIcon('');
                }
            }
        } catch (ilException $e) {
            $this->global_tpl->setOnScreenMessage("failure", $this->lng->txt('prg_type_msg_error_custom_icon'));

            return null;
        }

        return $type;
    }
}
