<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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


/**
* Class ilSCORM2004PageLayout
*
* Class for SCORM 2004 Page Layouts
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004PageLayout
{
    /**
     * @var ilDB
     */
    protected $db;

    
    const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/editor/page_layouts_temp/thumbnails';
    
    public $layout_id = null;
    
    public function __construct($a_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->layout_id = $a_id;
    }
    
    
    public function getXMLContent()
    {
        $ilDB = $this->db;
        $r = $ilDB->query("SELECT content FROM page_layout WHERE layout_id=" .
                                 $ilDB->quote($this->layout_id));
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $row['content'];
    }
    
    
    public function getPreview()
    {
        //just returns manually created previews at the moment
        return self::SEQ_TEMPLATE_DIR . "/" . $this->layout_id . ".png";
    }
    
    
    public function getTitle()
    {
        $ilDB = $this->db;

        $r = $ilDB->queryF(
            'SELECT title FROM page_layout WHERE layout_id = %s',
            array('integer'),
            array($this->layout_id)
        );
        $row = $ilDB->fetchAssoc($r);
        
        return $row['title'];
    }
    
    public function getId()
    {
        return $this->layout_id;
    }
    
    
    private function generatePreview()
    {
        
        //toimplement...generate Preview from XML
    }
    
    
    public static function activeLayouts()
    {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_layouts = array();
        $query = "SELECT * FROM page_layout WHERE (active=1) ORDER BY title ";
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            array_push($arr_layouts, new ilSCORM2004PageLayout($row['layout_id']));
        }
        return $arr_layouts;
    }
}
