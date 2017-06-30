<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

// note: the values are derived from ilObjCourse constants
// to enable easy migration from course view setting to container view setting

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilContainer
*
* Base class for all container objects (categories, courses, groups)
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @extends ilObject
*/
class ilContainer extends ilObject
{
	protected $order_type = 0;
	protected $hiddenfilesfound = false;
	protected $news_timeline = false;
	protected $news_timeline_auto_entries = false;

	// container view constants
	const VIEW_SESSIONS = 0;
	const VIEW_OBJECTIVE = 1;
	const VIEW_TIMING = 2;
	const VIEW_ARCHIVE = 3;
	const VIEW_SIMPLE = 4;
	const VIEW_BY_TYPE = 5;
	const VIEW_INHERIT = 6;

	const VIEW_DEFAULT = self::VIEW_BY_TYPE;

	
	const SORT_TITLE = 0;
	const SORT_MANUAL = 1;
	const SORT_ACTIVATION = 2;
	const SORT_INHERIT = 3;
	const SORT_CREATION = 4;
	
	const SORT_DIRECTION_ASC = 0;
	const SORT_DIRECTION_DESC = 1;

	const SORT_NEW_ITEMS_POSITION_TOP = 0;
	const SORT_NEW_ITEMS_POSITION_BOTTOM = 1;

	const SORT_NEW_ITEMS_ORDER_TITLE = 0;
	const SORT_NEW_ITEMS_ORDER_CREATION = 1;
	const SORT_NEW_ITEMS_ORDER_ACTIVATION = 2;


	static $data_preloaded = false;

	/**
	 * @var ilSetting
	 */
	protected $setting;

	function __construct($a_id = 0, $a_reference = true)
	{
		global $DIC;

		$this->setting = $DIC["ilSetting"];
		parent::__construct($a_id, $a_reference);

	}

	/**
	* Create directory for the container.
	* It is <webspace_dir>/container_data.
	*/
	function createContainerDirectory()
	{
		global $DIC;

		$webspace_dir = ilUtil::getWebspaceDir();
		$cont_dir = $webspace_dir."/container_data";
		if (!is_dir($cont_dir))
		{
			ilUtil::makeDir($cont_dir);
		}
		$obj_dir = $cont_dir."/obj_".$this->getId();
		if (!is_dir($obj_dir))
		{
			ilUtil::makeDir($obj_dir);
		}
	}
	
	/**
	* Get the container directory.
	*
	* @return	string	container directory
	*/
	function getContainerDirectory()
	{
		return $this->_getContainerDirectory($this->getId());
	}
	
	/**
	* Get the container directory.
	*
	* @return	string	container directory
	*/
	static function _getContainerDirectory($a_id)
	{
		return ilUtil::getWebspaceDir()."/container_data/obj_".$a_id;
	}
	
	/**
	 * Get path for big icon.
	 *
	 * @return	string	icon path
	 * @deprecated use _lookupIconPath instead
	 */
	function getBigIconPath()
	{
		return self::_lookupIconPath($this->getId());
	}

	/**
	 * Get path for small icon
	 *
	 * @return	string	icon path
	 * @deprecated use _lookupIconPath instead
	 */
	function getSmallIconPath()
	{
		return self::_lookupIconPath($this->getId());
	}

	/**
	 * Get path for tiny icon
	 *
	 * @return	string	icon path
	 * @deprecated use _lookupIconPath instead
	 */
	function getTinyIconPath()
	{
		return self::_lookupIconPath($this->getId());
	}

	/**
	 * Get path for custom icon
	 *
	 * @return	string	icon path
	 */
	function getCustomIconPath()
	{
		return self::_lookupIconPath($this->getId());
	}


	/**
	* Set Found hidden files (set by getSubItems).
	*
	* @param	boolean	$a_hiddenfilesfound	Found hidden files (set by getSubItems)
	*/
	function setHiddenFilesFound($a_hiddenfilesfound)
	{
		$this->hiddenfilesfound = $a_hiddenfilesfound;
	}

	/**
	* Get Found hidden files (set by getSubItems).
	*
	* @return	boolean	Found hidden files (set by getSubItems)
	*/
	function getHiddenFilesFound()
	{
		return $this->hiddenfilesfound;
	}

	/**
	* get ID of assigned style sheet object
	*/
	function getStyleSheetId()
	{
		return $this->style_id;
	}

	/**
	* set ID of assigned style sheet object
	*/
	function setStyleSheetId($a_style_id)
	{
		$this->style_id = $a_style_id;
	}

	/**
	 * Set news timeline
	 *
	 * @param bool $a_val activate news timeline
	 */
	function setNewsTimeline($a_val)
	{
		$this->news_timeline = $a_val;
	}

	/**
	 * Get news timeline
	 *
	 * @return bool activate news timeline
	 */
	function getNewsTimeline()
	{
		return $this->news_timeline;
	}
	
	/**
	 * Set news timeline auto entries
	 *
	 * @param bool $a_val include automatically created entries	
	 */
	function setNewsTimelineAutoEntries($a_val)
	{
		$this->news_timeline_auto_entries = $a_val;
	}
	
	/**
	 * Get news timeline auto entries
	 *
	 * @return bool include automatically created entries
	 */
	function getNewsTimelineAutoEntries()
	{
		return $this->news_timeline_auto_entries;
	}

	/**
	 * Set news timline is landing page
	 *
	 * @param bool $a_val is news timline landing page?
	 */
	function setNewsTimelineLandingPage($a_val)
	{
		$this->news_timeline_landing_page = $a_val;
	}

	/**
	 * Get news timline is landing page
	 *
	 * @return bool is news timline landing page?
	 */
	function getNewsTimelineLandingPage()
	{
		return $this->news_timeline_landing_page;
	}

	/**
	 * Set news block activated
	 *
	 * @param bool $a_val news block activated	
	 */
	function setNewsBlockActivated($a_val)
	{
		$this->news_block_activated = $a_val;
	}
	
	/**
	 * Get news block activated
	 *
	 * @return bool news block activated
	 */
	function getNewsBlockActivated()
	{
		return $this->news_block_activated;
	}
	
	/**
	 * Set use news
	 *
	 * @param bool $a_val use news system?	
	 */
	function setUseNews($a_val)
	{
		$this->use_news = $a_val;
	}
	
	/**
	 * Get use news
	 *
	 * @return bool use news system?
	 */
	function getUseNews()
	{
		return $this->use_news;
	}
	
	/**
	* Lookup a container setting.
	*
	* @param	int			container id
	* @param	string		setting keyword 
	*
	* @return	string		setting value
	*/
	static function _lookupContainerSetting($a_id, $a_keyword, $a_default_value = NULL)
	{
		global $ilDB;
		
		$q = "SELECT * FROM container_settings WHERE ".
				" id = ".$ilDB->quote($a_id ,'integer')." AND ".
				" keyword = ".$ilDB->quote($a_keyword ,'text');
		$set = $ilDB->query($q);
		$rec = $set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
		
		if(isset($rec['value']))
		{
			return $rec["value"];
		}
		if($a_default_value === NULL)
		{
			return '';
		}
		return $a_default_value;
	}

	/**
	 * @param $a_id
	 * @param $a_keyword
	 * @param $a_value
	 */
	public static function _writeContainerSetting($a_id, $a_keyword, $a_value)
	{
		global $ilDB;
		
		$query = "DELETE FROM container_settings WHERE ".
			"id = ".$ilDB->quote($a_id,'integer')." ".
			"AND keyword = ".$ilDB->quote($a_keyword,'text');
		$res = $ilDB->manipulate($query);

		$log = ilLoggerFactory::getLogger("cont");
		$log->debug("Write container setting, id: ".$a_id.", keyword: ".$a_keyword.", value: ".$a_value);

		$query = "INSERT INTO container_settings (id, keyword, value) VALUES (".
			$ilDB->quote($a_id ,'integer').", ".
			$ilDB->quote($a_keyword ,'text').", ".
			$ilDB->quote($a_value ,'text').
			")";

		$res = $ilDB->manipulate($query);
	}
	
	public static function _getContainerSettings($a_id)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT * FROM container_settings WHERE ".
				" id = ".$ilDB->quote($a_id ,'integer');
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["keyword"]] = $row["value"];			
		}		
		
		return $res;
	}	
	
	public static function _deleteContainerSettings($a_id, $a_keyword = null, $a_keyword_like = false)
	{
		global $ilDB;
		
		if(!$a_id)
		{
			return;
		}
		
		$sql = "DELETE FROM container_settings WHERE ".
				" id = ".$ilDB->quote($a_id ,'integer');
		if($a_keyword)
		{
			if(!$a_keyword_like)
			{
				$sql .= " AND keyword = ".$ilDB->quote($a_keyword, "text");
			}
			else
			{
				$sql .= " AND ".$ilDB->like("keyword", "text", $a_keyword);
			}
		}		
		$ilDB->manipulate($sql);		
	}	
	
	public static function _exportContainerSettings(ilXmlWriter $a_xml, $a_obj_id)
	{
		// container settings
		$settings = self::_getContainerSettings($a_obj_id);
		if(sizeof($settings))
		{
			$a_xml->xmlStartTag("ContainerSettings");
			
			foreach($settings as $keyword => $value)
			{
				// :TODO: proper custom icon export/import
				if(stristr($keyword, "icon"))
				{
					continue;
				}
				
				$a_xml->xmlStartTag(
					'ContainerSetting',
					array(
						'id' => $keyword,						
					)
				);
				
				$a_xml->xmlData($value);
				$a_xml->xmlEndTag("ContainerSetting");
			}
			
			$a_xml->xmlEndTag("ContainerSettings");
		}		
	}
	
	/**
	* lookup icon path
	*
	* @param	int		$a_id		container object id
	* @param	string	$a_size		"big" | "small"
	*/
	static function _lookupIconPath($a_id, $a_size = "big")
	{
		if ($a_size == "")
		{
			$a_size = "big";
		}
		$size = $a_size;
		
		if (ilContainer::_lookupContainerSetting($a_id, "icon_custom"))
		{
			$cont_dir = ilContainer::_getContainerDirectory($a_id);

			$file_name = $cont_dir."/icon_custom.svg";
			if (is_file($file_name))
			{
				return $file_name;
			}
		}
		
		return "";
	}

	/**
	* save container icons
	*/
	function saveIcons($a_custom_icon)
	{
		global $ilDB;
		
		$this->createContainerDirectory();
		$cont_dir = $this->getContainerDirectory();
		
		$file_name = "";
		if ($a_custom_icon != "")
		{
			$file_name = $cont_dir."/icon_custom.svg";
			ilUtil::moveUploadedFile($a_custom_icon, "icon_custom.svg", $file_name);

			if ($file_name != "" && is_file($file_name))
			{
				ilContainer::_writeContainerSetting($this->getId(), "icon_custom", 1);
			}
			else
			{
				ilContainer::_writeContainerSetting($this->getId(), "icon_custom", 0);
			}
		}
	}

	/**
	* remove small icon
	*/ 
	function removeCustomIcon()
	{
		$cont_dir = $this->getContainerDirectory();
		$small_file_name = $cont_dir."/icon_custom.svg";
		@unlink($small_file_name);
		ilContainer::_writeContainerSetting($this->getId(), "icon_custom", 0);
	}
	
	/**
	 * Clone container settings
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 * @return object new object 
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0, $a_omit_tree = false)
	{
		global $ilLog;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id, $a_omit_tree);
	
		include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
		#18624 - copy all sorting settings
		ilContainerSortingSettings::_cloneSettings($this->getId(), $new_obj->getId());
		
		// copy content page
		include_once("./Services/Container/classes/class.ilContainerPage.php");
		if (ilContainerPage::_exists("cont",
			$this->getId()))
		{
			$orig_page = new ilContainerPage($this->getId());
			$orig_page->copy($new_obj->getId(), "cont", $new_obj->getId());			
		}
		
		// #10271 - copy start objects page
		include_once("./Services/Container/classes/class.ilContainerStartObjectsPage.php");
		if (ilContainerStartObjectsPage::_exists("cstr",
			$this->getId()))
		{
			$orig_page = new ilContainerStartObjectsPage($this->getId());
			$orig_page->copy($new_obj->getId(), "cstr", $new_obj->getId());
		}
		
		// #10271 
		foreach(self::_getContainerSettings($this->getId()) as $keyword => $value)
		{						
			self::_writeContainerSetting($new_obj->getId(), $keyword, $value);
			
			// copy custom icons
			if($keyword == "icon_custom" && 
				$value)
			{
				// see saveIcons()
				$new_obj->createContainerDirectory();
				$tgt_dir = $new_obj->getContainerDirectory();
				$src_dir = $this->getContainerDirectory();				
				$file = "icon_custom.svg";
				$src_file = $src_dir."/".$file;
				if(file_exists($src_file))
				{
					copy($src_file, $tgt_dir."/".$file);
				}
			}
		}
		
		return $new_obj;
	}
	
	/**
	 * Clone object dependencies (container sorting)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 * return bool 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		global $ilLog;
		
		parent::cloneDependencies($a_target_id, $a_copy_id);

		include_once('./Services/Container/classes/class.ilContainerSorting.php');
		ilContainerSorting::_getInstance($this->getId())->cloneSorting($a_target_id,$a_copy_id);

		// fix internal links to other objects
		ilContainer::fixInternalLinksAfterCopy($a_target_id,$a_copy_id, $this->getRefId());
		
		// fix item group references in page content
		include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");
		ilObjItemGroup::fixContainerItemGroupRefsAfterCloning($this, $a_copy_id);
		
		include_once('Services/Object/classes/class.ilObjectLP.php');
		$olp = ilObjectLP::getInstance($this->getId());
		$collection = $olp->getCollectionInstance();
		if($collection)
		{
			$collection->cloneCollection($a_target_id, $a_copy_id);	 	
		}

		return true;
	}

	/**
	 * clone all objects according to this container
	 *
	 * @param string $session_id
	 * @param string $client_id
	 * @param string $new_type
	 * @param int $ref_id
	 * @param int $clone_source
	 * @param array $options
	 * @param bool force soap
	 * @param int submode 1 => copy all, 2 => copy content
	 * @return new refid if clone has finished or parameter ref id if cloning is still in progress
	 * @return array(copy_id => xyz, ref_id => new ref_id)
	 */
	public function cloneAllObject($session_id, $client_id, $new_type, $ref_id, $clone_source, $options, $soap_call = false, $a_submode = 1)
	{
		global $ilLog;
		
		include_once('./Services/Link/classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$tree,$ilUser;
			
		// Save wizard options
		$copy_id = ilCopyWizardOptions::_allocateCopyId();
		$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
		$wizard_options->saveOwner($ilUser->getId());
		$wizard_options->saveRoot($clone_source);
			
		// add entry for source container
		$wizard_options->initContainer($clone_source, $ref_id);
		
		foreach($options as $source_id => $option)
		{
			$wizard_options->addEntry($source_id,$option);
		}
		$wizard_options->read();
		$wizard_options->storeTree($clone_source);
		
		include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
		if($a_submode == ilObjectCopyGUI::SUBMODE_CONTENT_ONLY)
		{
			ilLoggerFactory::getLogger('obj')->info('Copy content only...');
			ilLoggerFactory::getLogger('obj')->debug('Added mapping, source ID: '.$clone_source.', target ID: '.$ref_id);
			$wizard_options->read();
			$wizard_options->dropFirstNode();
			$wizard_options->appendMapping($clone_source,$ref_id);
		}
		
		
		#print_r($options);
		// Duplicate session to avoid logout problems with backgrounded SOAP calls
		$new_session_id = ilSession::_duplicate($session_id);
		// Start cloning process using soap call
		include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

		$soap_client = new ilSoapClient();
		$soap_client->setResponseTimeout(5);
		$soap_client->enableWSDL(true);

		$ilLog->write(__METHOD__.': Trying to call Soap client...');
		if($soap_client->init())
		{
			ilLoggerFactory::getLogger('obj')->info('Calling soap clone method');
			$res = $soap_client->call('ilClone',array($new_session_id.'::'.$client_id, $copy_id));
		}
		else
		{
			ilLoggerFactory::getLogger('obj')->warning('SOAP clone call failed. Calling clone method manually');
			$wizard_options->disableSOAP();
			$wizard_options->read();			
			include_once('./webservice/soap/include/inc.soap_functions.php');
			$res = ilSoapFunctions::ilClone($new_session_id.'::'.$client_id, $copy_id);
		}
		return array(
				'copy_id' => $copy_id,
				'ref_id' => (int) $res
		);
	}
	
	/**
	* Get container view mode
	*/
	function getViewMode()
	{
		return ilContainer::VIEW_BY_TYPE;
	}
	
	/**
	* Get order type default implementation
	*/
	function getOrderType()
	{
		return $this->order_type ? $this->order_type : ilContainer::SORT_TITLE;
	}

	function setOrderType($a_value)
	{
		$this->order_type = $a_value;
	}
	
	/**
	* Get subitems of container
	* 
	* @param bool administration panel enabled
	* @param bool side blocks enabled
	*
	* @return	array
	*/
	public function getSubItems($a_admin_panel_enabled = false, $a_include_side_block = false, $a_get_single = 0)
	{
		global $objDefinition, $ilBench, $tree, $ilObjDataCache, $ilUser, $rbacsystem,
			$ilSetting;

		// Caching
		if (is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]) &&
			!$a_get_single)
		{
			return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
		}
		
		$type_grps = $this->getGroupedObjTypes();
		$objects = $tree->getChilds($this->getRefId(), "title");

		$objects = self::getCompleteDescriptions($objects);

		$found = false;
		$all_ref_ids = array();
		
		if(!self::$data_preloaded)
		{
			include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
			$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_REPOSITORY);		
		}		

		include_once('Services/Container/classes/class.ilContainerSorting.php');
		$sort = ilContainerSorting::_getInstance($this->getId());

		// TODO: check this
		// get items attached to a session
		include_once './Modules/Session/classes/class.ilEventItems.php';
		$event_items = ilEventItems::_getItemsOfContainer($this->getRefId());
		
		foreach ($objects as $key => $object)
		{
			if ($a_get_single > 0 && $object["child"] != $a_get_single)
			{
				continue;
			}
			
			// hide object types in devmode
			if ($objDefinition->getDevMode($object["type"]) || $object["type"] == "adm"
				|| $object["type"] == "rolf")
			{
				continue;
			}
			
			// remove inactive plugins
			if ($objDefinition->isInactivePlugin($object["type"]))
			{
				continue;
			}

			// BEGIN WebDAV: Don't display hidden Files, Folders and Categories
			if (in_array($object['type'], array('file','fold','cat')))
			{
				include_once 'Modules/File/classes/class.ilObjFileAccess.php';
				if (ilObjFileAccess::_isFileHidden($object['title']))
				{
					$this->setHiddenFilesFound(true);
					if (!$a_admin_panel_enabled)
					{
						continue;
					}
				}
			}
			// END WebDAV: Don't display hidden Files, Folders and Categories
			
			// including event items!
			if (!self::$data_preloaded)
			{
				$preloader->addItem($object["obj_id"], $object["type"], $object["child"]);
			}			
			
			// filter out items that are attached to an event
			if (in_array($object['ref_id'],$event_items))
			{
				continue;
			}
			
			// filter side block items
			if(!$a_include_side_block && $objDefinition->isSideBlock($object['type']))
			{
				continue;
			}

			$all_ref_ids[] = $object["child"];
		}
						
		// data preloader
		if (!self::$data_preloaded)
		{
			$preloader->preload();
			unset($preloader);
			
			self::$data_preloaded = true;
		}
		
		foreach($objects as $key => $object)
		{					
			// see above, objects were filtered
			if(!in_array($object["child"], $all_ref_ids))
			{
				continue;
			}
			
			// group object type groups together (e.g. learning resources)
			$type = $objDefinition->getGroupOfObj($object["type"]);
			if ($type == "")
			{
				$type = $object["type"];
			}
			
			// this will add activation properties
			$this->addAdditionalSubItemInformation($object);
			
			$this->items[$type][$key] = $object;
						
			$this->items["_all"][$key] = $object;
			if ($object["type"] != "sess")
			{
				$this->items["_non_sess"][$key] = $object;
			}
		}
		
		$this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]
			= $sort->sortItems($this->items);

		return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
	}
	
	/**
	* Check whether we got any items
	*/
	function gotItems()
	{
		if (is_array($this->items["_all"]) && count($this->items["_all"]) > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	* Add additional information to sub item, e.g. used in
	* courses for timings information etc.
	*/
	function addAdditionalSubItemInformation(&$object)
	{
	}
	
	/**
	* Get grouped repository object types.
	*
	* @return	array	array of object types
	*/
	function getGroupedObjTypes()
	{
		global $objDefinition;
		
		if (empty($this->type_grps))
		{
			$this->type_grps = $objDefinition->getGroupedRepositoryObjectTypes($this->getType());
		}
		return $this->type_grps;
	}
	
	/**
	* Check whether page editing is allowed for container
	*/
	function enablePageEditing()
	{
		global $ilSetting;
		
		// @todo: this will need a more general approach
		if ($ilSetting->get("enable_cat_page_edit"))
		{
			return true;
		}
	}
	
	/**
	* Create
	*/
	function create()
	{
		$ret = parent::create();
		
		if (((int) $this->getStyleSheetId()) > 0)
		{
			include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
			ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());
		}

		$log = ilLoggerFactory::getLogger("cont");
		$log->debug("Create Container, id: ".$this->getId());

		self::_writeContainerSetting($this->getId(), "news_timeline", (int) $this->getNewsTimeline());
		self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (int) $this->getNewsTimelineAutoEntries());
		self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (int) $this->getNewsTimelineLandingPage());
		include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
		self::_writeContainerSetting($this->getId() ,ilObjectServiceSettingsGUI::NEWS_VISIBILITY,(int) $this->getNewsBlockActivated());
		self::_writeContainerSetting($this->getId() ,ilObjectServiceSettingsGUI::USE_NEWS,(int) $this->getUseNews());

		return $ret;
	}
	
	/**
	* Update
	*/
	function update()
	{
		$ret = parent::update();
		
		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		ilObjStyleSheet::writeStyleUsage($this->getId(), $this->getStyleSheetId());

		$log = ilLoggerFactory::getLogger("cont");
		$log->debug("Update Container, id: ".$this->getId());

		self::_writeContainerSetting($this->getId(), "news_timeline", (int) $this->getNewsTimeline());
		self::_writeContainerSetting($this->getId(), "news_timeline_incl_auto", (int) $this->getNewsTimelineAutoEntries());
		self::_writeContainerSetting($this->getId(), "news_timeline_landing_page", (int) $this->getNewsTimelineLandingPage());
		include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
		self::_writeContainerSetting($this->getId() ,ilObjectServiceSettingsGUI::NEWS_VISIBILITY,(int) $this->getNewsBlockActivated());
		self::_writeContainerSetting($this->getId() ,ilObjectServiceSettingsGUI::USE_NEWS,(int) $this->getUseNews());

		return $ret;
	}
	
	
	/**
	 * read
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function read()
	{
		parent::read();
		
		include_once("./Services/Container/classes/class.ilContainerSortingSettings.php");
		$this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
		
		include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
		$this->setStyleSheetId((int) ilObjStyleSheet::lookupObjectStyle($this->getId()));

		$this->readContainerSettings();
	}

	/**
	 * Read container settings
	 *
	 * @param
	 * @return
	 */
	function readContainerSettings()
	{
		$this->setNewsTimeline(self::_lookupContainerSetting($this->getId(), "news_timeline"));
		$this->setNewsTimelineAutoEntries(self::_lookupContainerSetting($this->getId(), "news_timeline_incl_auto"));
		$this->setNewsTimelineLandingPage(self::_lookupContainerSetting($this->getId(), "news_timeline_landing_page"));
		include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
		$this->setNewsBlockActivated(self::_lookupContainerSetting($this->getId(), ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
			$this->setting->get('block_activated_news',true)));
		$this->setUseNews(self::_lookupContainerSetting($this->getId(), ilObjectServiceSettingsGUI::USE_NEWS, true));
	}


	/**
	 * overwrites description fields to long or short description in an assoc array
	 * keys needed (obj_id and description)
	 *
	 * @param array $objects
	 * @return array
	 */
	public static function getCompleteDescriptions(array $objects)
	{
		global $ilSetting, $ilObjDataCache;
		// using long descriptions?
		$short_desc = $ilSetting->get("rep_shorten_description");
		$short_desc_max_length = $ilSetting->get("rep_shorten_description_length");
		if(!$short_desc || $short_desc_max_length != ilObject::DESC_LENGTH)
		{
			// using (part of) shortened description
			if($short_desc && $short_desc_max_length && $short_desc_max_length < ilObject::DESC_LENGTH)
			{
				foreach($objects as $key => $object)
				{
					$objects[$key]["description"] = ilUtil::shortenText($object["description"], $short_desc_max_length, true);
				}
			}
			// using (part of) long description
			else
			{
				$obj_ids = array();
				foreach($objects as $key => $object)
				{
					$obj_ids[] = $object["obj_id"];
				}
				if(sizeof($obj_ids))
				{
					$long_desc = ilObject::getLongDescriptions($obj_ids);
					foreach($objects as $key => $object)
					{
						// #12166 - keep translation, ignore long description
						if($ilObjDataCache->isTranslatedDescription($object["obj_id"]))
						{
							$long_desc[$object["obj_id"]] = $object["description"];
						}
						if($short_desc && $short_desc_max_length)
						{
							$long_desc[$object["obj_id"]] = ilUtil::shortenText($long_desc[$object["obj_id"]], $short_desc_max_length, true);
						}
						$objects[$key]["description"] = $long_desc[$object["obj_id"]];
					}
				}
			}
		}
		return $objects;
	}

	/**
	 * Fix internal links after copy process
	 *
	 * @param int $a_target_id ref if of new container
	 * @param int $a_copy_id copy process id
	 */
	protected static function fixInternalLinksAfterCopy($a_target_id, $a_copy_id, $a_source_ref_id)
	{
		$obj_id = ilObject::_lookupObjId($a_target_id);
		include_once("./Services/Container/classes/class.ilContainerPage.php");
		if (ilContainerPage::_exists("cont", $obj_id))
		{
			include_once("./Services/CopyWizard/classes/class.ilCopyWizardOptions.php");
			$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
			$mapping = $cwo->getMappings();
			$pg = new ilContainerPage($obj_id);
			$pg->handleRepositoryLinksOnCopy($mapping, $a_source_ref_id);
			$pg->update(true, true);
		}
	}
	
} // END class ilContainer
?>
