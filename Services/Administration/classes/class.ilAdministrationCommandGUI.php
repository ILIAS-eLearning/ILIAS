<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles Administration commands (cut, delete paste)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilAdministrationCommandGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    protected $ctrl = null;
    protected $lng = null;
    private $container = null;

    /**
     * Constructor
     */
    public function __construct($a_container)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->settings = $DIC->settings();
        $this->error = $DIC["ilErr"];
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->container = $a_container;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
    }

    /**
     * Get container object
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Show delete confirmation
     */
    public function delete()
    {
        $tpl = $this->tpl;
        $ilSetting = $this->settings;
        $ilErr = $this->error;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $to_delete = array();
        if ((int) $_GET['item_ref_id']) {
            $to_delete = array(
                (int) $_GET['item_ref_id']
            );
        }

        if (isset($_POST['id']) and is_array($_POST['id'])) {
            $to_delete = $_POST['id'];
        }

        if (!$to_delete) {
            $ilErr->raiseError($this->lng->txt('no_checkbox'), $ilErr->MESSAGE);
        }

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormActionByClass(get_class($this->getContainer()), 'cancel'));
        $confirm->setHeaderText('');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancelDelete');
        $confirm->setConfirm($this->lng->txt('delete'), 'performDelete');

        foreach ($to_delete as $delete) {
            $obj_id = ilObject :: _lookupObjId($delete);
            $type = ilObject :: _lookupType($obj_id);
            
            $confirm->addItem(
                'id[]',
                $delete,
                call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id),
                ilUtil :: getTypeIconPath($type, $obj_id)
            );
        }

        $msg = $this->lng->txt("info_delete_sure");
            
        if (!$ilSetting->get('enable_trash')) {
            $msg .= "<br/>" . $this->lng->txt("info_delete_warning_no_trash");
        }
        ilUtil::sendQuestion($msg);

        $tpl->setContent($confirm->getHTML());
    }

    /**
     * Perform delete
     */
    public function performDelete()
    {
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $_SESSION['saved_post'] = $_POST['id'];
        $object = new ilObjectGUI(array(), 0, false, false);
        $object->confirmedDeleteObject();
        return true;
    }

    /**
     * Cut object
     */
    public function cut()
    {
        $tree = $this->tree;
        
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $_GET['ref_id'] = $tree->getParentId((int) $_GET['item_ref_id']);

        $container = new ilContainerGUI(array(), 0, false, false);
        $container->cutObject();
        return true;
    }
    
    /**
     * Show target selection
     * @return
     */
    public function showMoveIntoObjectTree()
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
        $type = ilObject :: _lookupType($obj_id);

        $location = $objDefinition->getLocation($type);
        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), (int) $_GET['ref_id'], true, false);
        $container->showMoveIntoObjectTreeObject();
        return true;
    }
    
    /**
     * Target selection
     * @return
     */
    public function showLinkIntoMultipleObjectsTree()
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
        $type = ilObject :: _lookupType($obj_id);

        $location = $objDefinition->getLocation($type);
        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), (int) $_GET['ref_id'], true, false);
        $container->showLinkIntoMultipleObjectsTreeObject();
        return true;
    }

    /**
     * Start linking object
     */
    public function link()
    {
        $tree = $this->tree;
        
        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $_GET['ref_id'] = $tree->getParentId((int) $_GET['item_ref_id']);

        $container = new ilContainerGUI(array(), 0, false, false);
        $container->linkObject();
        return true;
    }

    /**
     * Paste object
     */
    public function paste()
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');
        $_GET['ref_id'] = (int) $_GET['item_ref_id'];

        $obj_id = ilObject :: _lookupObjId((int) $_GET['item_ref_id']);
        $type = ilObject :: _lookupType($obj_id);

        $location = $objDefinition->getLocation($type);
        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), (int) $_GET['item_ref_id'], true, false);
        $container->pasteObject();
        return true;
    }
    
    public function performPasteIntoMultipleObjects()
    {
        $objDefinition = $this->obj_definition;

        $this->ctrl->setReturnByClass(get_class($this->getContainer()), '');

        $obj_id = ilObject :: _lookupObjId((int) $_GET['ref_id']);
        $type = ilObject :: _lookupType($obj_id);

        $location = $objDefinition->getLocation($type);
        $class_name = "ilObj" . $objDefinition->getClassName($type) . 'GUI';

        // create instance
        $container = new $class_name(array(), (int) $_GET['ref_id'], true, false);
        $container->performPasteIntoMultipleObjectsObject();
        return true;
    }
}
