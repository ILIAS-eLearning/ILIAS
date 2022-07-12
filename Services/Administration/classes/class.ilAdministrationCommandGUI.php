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

use ILIAS\Administration\AdminGUIRequest;

/**
 * Handles Administration commands (cut, delete paste)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilAdministrationCommandGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilSetting $settings;
    protected ilErrorHandling $error;
    protected ilTree $tree;
    protected ilObjectDefinition$obj_definition;
    protected ?ilCtrl $ctrl = null;
    protected ?ilLanguage $lng = null;
    private ilAdministrationCommandHandling $container;
    protected AdminGUIRequest $request;

    public function __construct(ilAdministrationCommandHandling $a_container)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
        $this->error = $DIC["ilErr"];
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->container = $a_container;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->request = new AdminGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function getContainer() : ilAdministrationCommandHandling
    {
        return $this->container;
    }

    public function delete() : void
    {
        $tpl = $this->tpl;
        $ilSetting = $this->settings;
        $ilErr = $this->error;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $to_delete = $this->request->getSelectedIds();

        if (count($to_delete) === 0) {
            $ilErr->raiseError($this->lng->txt('no_checkbox'), $ilErr->MESSAGE);
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormActionByClass(get_class($this->getContainer()), 'cancel'));
        $confirm->setHeaderText('');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancelDelete');
        $confirm->setConfirm($this->lng->txt('delete'), 'performDelete');

        foreach ($to_delete as $delete) {
            $obj_id = ilObject::_lookupObjId($delete);
            $type = ilObject::_lookupType($obj_id);
            
            $confirm->addItem(
                'id[]',
                $delete,
                call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id),
                ilObject::_getIcon($obj_id, 'small', $type)
            );
        }

        $msg = $this->lng->txt("info_delete_sure");
        
        if (!$ilSetting->get('enable_trash')) {
            $msg .= "<br/>" . $this->lng->txt("info_delete_warning_no_trash");
        }
        $this->tpl->setOnScreenMessage('question', $msg);

        $tpl->setContent($confirm->getHTML());
    }

    public function performDelete() : void
    {
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        ilSession::set("saved_post", $this->request->getSelectedIds());

        $object = new ilObjectGUI(array(), 0, false, false);
        $object->confirmedDeleteObject();
    }

    public function cut() : void
    {
        $tree = $this->tree;
        
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $ref_id = $tree->getParentId($this->request->getItemRefId());

        $container = new ilContainerGUI(array(), $ref_id, true, false);
        $container->cutObject();
    }
    
    // Show target selection
    public function showMoveIntoObjectTree() : void
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject::_lookupObjId($this->request->getRefId());
        $type = ilObject::_lookupType($obj_id);

        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), $this->request->getRefId(), true, false);
        $container->showMoveIntoObjectTreeObject();
    }
    
    // Target selection
    public function showLinkIntoMultipleObjectsTree() : void
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject::_lookupObjId($this->request->getRefId());
        $type = ilObject::_lookupType($obj_id);

        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), $this->request->getRefId(), true, false);
        $container->showLinkIntoMultipleObjectsTreeObject();
    }

    // Start linking object
    public function link() : void
    {
        $tree = $this->tree;
        
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $ref_id = $tree->getParentId($this->request->getItemRefId());

        $container = new ilContainerGUI(array(), $ref_id, true, false);
        $container->linkObject();
    }

    // Paste object
    public function paste() : void
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject::_lookupObjId($this->request->getItemRefId());
        $type = ilObject::_lookupType($obj_id);

        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), $this->request->getItemRefId(), true, false);
        $container->pasteObject();
    }
    
    public function performPasteIntoMultipleObjects() : void
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject::_lookupObjId($this->request->getRefId());
        $type = ilObject::_lookupType($obj_id);

        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), $this->request->getRefId(), true, false);
        $container->performPasteIntoMultipleObjectsObject();
    }
}
