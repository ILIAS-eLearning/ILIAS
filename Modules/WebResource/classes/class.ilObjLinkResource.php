<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once "./Services/Object/classes/class.ilObject.php";

/** @defgroup ModulesWebResource Modules/WebResource
 */

/**
* Class ilObjLinkResource
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesWebResource
*/
class ilObjLinkResource extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "webr";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * create object
    *
    * @param bool upload mode (if enabled no meta data will be created)
    */
    public function create($a_upload = false)
    {
        $new_id = parent::create();
        
        if (!$a_upload) {
            $this->createMetaData();
        }
        
        return $new_id;
    }

    /**
    * update object
    */
    public function update()
    {
        $this->updateMetaData();
        parent::update();
    }
    
    /**
     * Overwriten Metadata update listener for ECS functionalities
     *
     * @access public
     *
     */
    public function MDUpdateListener($a_element)
    {
        global $DIC;

        parent::MDUpdateListener($a_element);
        
        $md = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($md_gen = $md->getGeneral())) {
            return false;
        }
        $title = $md_gen->getTitle();
        foreach ($md_gen->getDescriptionIds() as $id) {
            $md_des = $md_gen->getDescription($id);
            $description = $md_des->getDescription();
            break;
        }
        switch ($a_element) {
            case 'General':
                    include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
                    if (ilLinkResourceItems::lookupNumberOfLinks($this->getId()) == 1) {
                        $link_arr = ilLinkResourceItems::_getFirstLink($this->getId());
                        $link = new ilLinkResourceItems($this->getId());
                        $link->readItem($link_arr['link_id']);
                        $link->setTitle($title);
                        $link->setDescription($description);
                        $link->update();
                    }
                    break;
            default:
                return true;
        }
        return true;
    }
    


    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete items
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        ilLinkResourceItems::_deleteAll($this->getId());


        // Delete notify entries
        include_once './Services/LinkChecker/classes/class.ilLinkCheckNotify.php';
        ilLinkCheckNotify::_deleteObject($this->getId());

        // delete meta data
        $this->deleteMetaData();

        return true;
    }

    public function initLinkResourceItemsObject()
    {
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';

        $this->items_obj = new ilLinkResourceItems($this->getId());

        return true;
    }
    
    /**
     * Clone
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $this->cloneMetaData($new_obj);
        
        // object created now copy other settings
        include_once('Modules/WebResource/classes/class.ilLinkResourceItems.php');
        $links = new ilLinkResourceItems($this->getId());
        $links->cloneItems($new_obj->getId());
        
        // append copy info weblink title
        if (ilLinkResourceItems::_isSingular($new_obj->getId())) {
            $first = ilLinkResourceItems::_getFirstLink($new_obj->getId());
            ilLinkResourceItems::updateTitle($first['link_id'], $new_obj->getTitle());
        }
        
        return $new_obj;
    }

    /**
     * Write webresource xml
     * @param ilXmlWriter $writer
     * @return
     */
    public function toXML(ilXmlWriter $writer)
    {
        $attribs = array("obj_id" => "il_" . IL_INST_ID . "_webr_" . $this->getId());

        $writer->xmlStartTag('WebLinks', $attribs);
                
        // LOM MetaData
        include_once 'Services/MetaData/classes/class.ilMD2XML.php';
        $md2xml = new ilMD2XML($this->getId(), $this->getId(), 'webr');
        $md2xml->startExport();
        $writer->appendXML($md2xml->getXML());

        // Sorting
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        switch (ilContainerSortingSettings::_lookupSortMode($this->getId())) {
            case ilContainer::SORT_MANUAL:
                $writer->xmlElement(
                    'Sorting',
                    array('type'	=> 'Manual')
                );
                break;
            
            case ilContainer::SORT_TITLE:
            default:
                $writer->xmlElement(
                    'Sorting',
                    array('type'	=> 'Title')
                );
                break;
        }
        
        // All links
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $links = new ilLinkResourceItems($this->getId());
        $links->toXML($writer);
        
        
        $writer->xmlEndTag('WebLinks');
        return true;
    }


    // PRIVATE
} // END class.ilObjLinkResource
