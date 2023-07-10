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

/**
 * Bibliographic Libraries Form.
 *
 * @author       Theodor Truffer
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 */
class ilBiblLibraryFormGUI extends ilPropertyFormGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    protected \ilBiblLibraryInterface $object;


    /**
     * ilBiblLibraryFormGUI constructor.
     */
    public function __construct(ilBiblLibraryInterface $library)
    {
        $this->object = $library;
        $this->ctrl()->saveParameterByClass(ilBiblLibraryGUI::class, ilBiblLibraryGUI::F_LIB_ID);
        $this->initForm();

        if ($this->object->getId()) {
            $this->fillForm();
        }
        parent::__construct();
    }


    /**
     * Init settings property form
     *
     * @access private
     */
    private function initForm(): void
    {
        $this->setFormAction($this->ctrl()->getFormActionByClass(ilBiblLibraryGUI::class));
        $name = new ilTextInputGUI($this->lng()->txt("bibl_library_name"), 'name');
        $name->setRequired(true);
        $name->setValue('');
        $this->addItem($name);
        $url = new ilTextInputGUI($this->lng()->txt("bibl_library_url"), 'url');
        $url->setRequired(true);
        $url->setValue('');
        $this->addItem($url);
        $img = new ilTextInputGUI($this->lng()->txt("bibl_library_img"), 'img');
        $img->setValue('');
        $this->addItem($img);
        $show_in_list = new ilCheckboxInputGUI($this->lng()
            ->txt("bibl_library_show_in_list"), 'show_in_list');
        $show_in_list->setValue('1');
        $this->addItem($show_in_list);
        if ($this->object->getId()) {
            $this->addCommandButton('update', $this->lng()->txt('save'));
            $this->fillForm();
            $this->setTitle($this->lng()->txt("bibl_settings_edit"));
        } else {
            $this->setTitle($this->lng()->txt("bibl_settings_new"));
            $this->addCommandButton('create', $this->lng()->txt('save'));
        }
        $this->addCommandButton('cancel', $this->lng()->txt("cancel"));
    }


    private function fillForm(): void
    {
        $this->setValuesByArray(array(
            'name' => $this->object->getName(),
            'url' => $this->object->getUrl(),
            'img' => $this->object->getImg(),
            'show_in_list' => $this->object->isShownInList(),
        ));
    }


    public function saveObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }
        $this->object->setName($this->getInput("name"));
        $this->object->setUrl($this->getInput("url"));
        $this->object->setImg($this->getInput("img"));
        $this->object->setShowInList((bool) $this->getInput("show_in_list"));
        $this->object->store();

        return true;
    }
}
