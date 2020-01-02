<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/Xml/classes/class.ilSaxParser.php");
require_once('./Services/User/classes/class.ilObjUser.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');


/**
 * Group Import Parser
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @extends ilSaxParser

 */
class ilCategoryXmlParser extends ilSaxParser
{
    /**
     * @var ilErrorHandling
     */
    protected $error;

    const MODE_CREATE = 1;
    const MODE_UPDATE = 2;
    
    private $cat = null;
    private $parent_id = 0;
    
    private $current_translation = array();
    private $current_container_setting;


    /**
     * @var ilLogger
     */
    protected $cat_log;

    /**
     * Constructor
     *
     * @param	string		$a_xml_file		xml file
     *
     * @access	public
     */

    public function __construct($a_xml, $a_parent_id)
    {
        global $DIC;

        $this->error = $DIC["ilErr"];
        parent::__construct(null);

        $this->mode = ilCategoryXmlParser::MODE_CREATE;
        $this->parent_id = $a_parent_id;
        $this->setXMLContent($a_xml);

        $this->cat_log = ilLoggerFactory::getLogger("cat");
    }
    
    /**
     * Get parent id
     * @return type
     */
    public function getParentId()
    {
        return $this->parent_id;
    }
    
    /**
     * Get current translation
     */
    protected function getCurrentTranslation()
    {
        return $this->current_translation;
    }
    


    /**
     * set event handler
     * should be overwritten by inherited class
     * @access	private
     */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
     * start the parser
     */
    public function startParsing()
    {
        parent::startParsing();

        if ($this->mode == ilCategoryXmlParser::MODE_CREATE) {
            return is_object($this->cat) ? $this->cat->getRefId() : false;
        } else {
            return is_object($this->cat) ? $this->cat->update() : false;
        }
    }


    /**
     * handler for begin of element
     */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        $ilErr = $this->error;

        switch ($a_name) {
            case "Category":
                break;
            
            case 'Translations':
                $this->getCategory()->removeTranslations();
                break;
            
            case 'Translation':
                $this->current_translation = array();
                $this->current_translation['default'] = $a_attribs['default'] ? 1 : 0;
                $this->current_translation['lang'] = $a_attribs['language'];
                break;

            case 'Sorting':
            case 'Sort':
                include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
                ilContainerSortingSettings::_importContainerSortingSettings($a_attribs, $this->getCategory()->getId());
                break;
            
            case 'ContainerSetting':
                $this->current_container_setting = $a_attribs['id'];
                break;
        }
    }


    /**
     * Handler end tag
     * @param type $a_xml_parser
     * @param type $a_name
     */
    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case "Category":
                $this->save();
                break;
            
            case 'Title':
                $this->current_translation['title'] = trim($this->cdata);
                
                if ($this->current_translation['default']) {
                    $this->getCategory()->setTitle(trim($this->cdata));
                }
                
                break;
            
            case 'Description':
                $this->current_translation['description'] = trim($this->cdata);
                
                if ($this->current_translation['default']) {
                    $this->getCategory()->setDescription(trim($this->cdata));
                }
                
                break;
            
            case 'Translation':
                // Add translation
                $this->getCategory()->addTranslation(
                    (string) $this->current_translation['title'],
                    (string) $this->current_translation['description'],
                    (string) $this->current_translation['lang'],
                    (int) $this->current_translation['default']
                );
                break;
            
            case 'ContainerSetting':
                if ($this->current_container_setting) {
                    $this->cat_log->debug("Write container Setting, ID: " . $this->getCategory()->getId() . ", setting: " .
                        $this->current_container_setting . ", data: " . $this->cdata);
                    ilContainer::_writeContainerSetting(
                        $this->getCategory()->getId(),
                        $this->current_container_setting,
                        $this->cdata
                    );
                }
                break;

            case 'ContainerSettings':
                $this->cat->readContainerSettings();	// read container settings to member vars (call getter/setter), see #0019870
                break;
        }
        $this->cdata = '';
    }


    /**
     * handler for character data
     */
    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        #$a_data = str_replace("<","&lt;",$a_data);
        #$a_data = str_replace(">","&gt;",$a_data);

        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }

    /**
     * Save category object
     * @return type
     */
    protected function save()
    {

        /**
         * mode can be create or update
         */
        if ($this->mode == ilCategoryXmlParser::MODE_CREATE) {
            $this->create();
            $this->getCategory()->create();
            $this->getCategory()->createReference();
            $this->getCategory()->putInTree($this->getParentId());
            $this->getCategory()->setPermissions($this->getParentId());
        }
        $this->getCategory()->update();
        return true;
    }




    /**
     * Set import mode
     * @param type $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Set category object
     * @param type $cat
     */
    public function setCategory($cat)
    {
        $this->cat = $cat;
    }
    
    public function getCategory()
    {
        return $this->cat;
    }
}
