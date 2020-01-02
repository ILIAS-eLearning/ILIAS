<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Object/classes/class.ilObjectListGUI.php');

/**
 * Item group list gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesItemGroup
 */
class ilObjItemGroupListGUI extends ilObjectListGUI
{
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $lng->loadLanguageModule('itgr');
        parent::__construct();
    }
    
    /**
     * Initialisation
     *
     * @access public
     * @return void
     */
    public function init()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = false;
        $this->subitems_enabled = true;
        $this->type = "itgr";
        $this->gui_class_name = "ilobjitemgroupgui";
        
        // general commands array
        include_once('./Modules/ItemGroup/classes/class.ilObjItemGroupAccess.php');
        $this->commands = ilObjItemGroupAccess::_getCommands();
    }

    /**
     * Enable subscribtion (deactivated)
     * necessary due to bug 11509
     *
     * @param
     * @return
     */
    public function enableSubscribe($a_val)
    {
        $this->subscribe_enabled = false;
    }
    
    /**
     * Prevent enabling info
     * necessary due to bug 11509
     *
     * @param bool
     * @return void
     */
    public function enableInfoScreen($a_info_screen)
    {
        $this->info_screen_enabled = false;
    }


    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        // separate method for this line
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
        return $cmd_link;
    }
    
    
    /**
     * Fet properties
     *
     * @return array properties array
     */
    public function getProperties()
    {
        $props = array();
        return $props;
    }

    
    
    /**
     * Get assigned items of event.
     * @return
     * @param object $a_sess_id
     */
    protected static function lookupAssignedMaterials($a_sess_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        return array();
        /*
                $query = 'SELECT * FROM event_items '.
                    'WHERE event_id = '.$ilDB->quote($a_sess_id).' ';
                $res = $ilDB->query($query);
                while($row = $res->fetchRow(FETCHMODE_OBJECT))
                {
                    $items[] = $row['item_id'];
                }
                return $items ? $items : array();*/
    }
}
