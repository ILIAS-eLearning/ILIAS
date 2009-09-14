<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define ("IL_LIST_AS_TRIGGER", "trigger");
define ("IL_LIST_FULL", "full");

include_once 'payment/classes/class.ilGeneralSettings.php';

/**
* Class ilObjectListGUI
*
* Important note:
*
* All access checking should be made within $ilAccess and
* the checkAccess of the ilObj...Access classes. Do not additionally
* enable or disable any commands within this GUI class or in derived
* classes, except when the container (e.g. a search result list)
* generally refuses them.
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
*/
class ilObjectListGUI
{
	var $ctrl;
	var $description_enabled = true;
	var $preconditions_enabled = true;
	var $properties_enabled = true;
	var $notice_properties_enabled = true;
	var $commands_enabled = true;
	var $cust_prop = array();
	var $cust_commands = array();
	var $info_screen_enabled = false;
	var $condition_depth = 0;
	var $std_cmd_only = false;
	var $sub_item_html = array();

	protected $substitutions = null;
	protected $substitutions_enabled = false;
	
	protected $icons_enabled = false;
	protected $checkboxes_enabled = false;
	protected $position_enabled = false;
	protected $progress_enabled = false;
	protected $item_detail_links_enabled = false;
	protected $item_detail_links = array();
	protected $item_detail_links_intro = '';
	
	protected $search_fragments_enabled = false;
	protected $search_fragment = '';
	protected $path_linked = false;

	protected $enabled_relevance = false;
	protected $relevance = 0; 

	protected $expand_enabled = false;
	protected $is_expanded = true;
	protected $bold_title = false;
	
	protected $copy_enabled = true;
	
	/**
	* constructor
	*
	*/
	function ilObjectListGUI()
	{
		global $rbacsystem, $ilCtrl, $lng, $ilias;

		$this->rbacsystem = $rbacsystem;
		$this->ilias = $ilias;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->mode = IL_LIST_FULL;
		$this->path_enabled = false;
		
//echo "list";
		$this->init();
		
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
		$this->ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
	}


	/**
	* set the container object (e.g categorygui)
	* Used for link, delete ... commands
	*
	* this method should be overwritten by derived classes
	*/
	function setContainerObject(&$container_obj)
	{
		$this->container_obj =& $container_obj;
	}
	
	/**
	 * get container object
	 *
	 * @access public
	 * @param
	 * @return object container
	 */
	public function getContainerObject()
	{
		return $this->container_obj;
	}


	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		// Create static links for default command (linked title) or not
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->copy_enabled = false;
		$this->payment_enabled = false;
		$this->progress_enabled = false;
		$this->notice_properties_enabled = true;
		$this->info_screen_enabled = false;
		$this->type = "";					// "cat", "course", ...
		$this->gui_class_name = "";			// "ilobjcategorygui", "ilobjcoursegui", ...

		// general commands array, e.g.
		include_once('class.ilObjectAccess.php');
		$this->commands = ilObjectAccess::_getCommands();
	}

	// Single get set methods
	/**
	* En/disable properties
	*
	* @param bool
	* @return void
	*/
	function enableProperties($a_status)
	{
		$this->properties_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getPropertiesStatus()
	{
		return $this->properties_enabled;
	}
	/**
	* En/disable preconditions
	*
	* @param bool
	* @return void
	*/
	function enablePreconditions($a_status)
	{
		$this->preconditions_enabled = $a_status;

		return;
	}
	
	function getNoticePropertiesStatus()
	{
		return $this->notice_properties_enabled;
	}
	
	/**
	* En/disable notices
	*
	* @param bool
	* @return void
	*/
	function enableNoticeProperties($a_status)
	{
		$this->notice_properties_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getPreconditionsStatus()
	{
		return $this->preconditions_enabled;
	}
	/**
	* En/disable description
	*
	* @param bool
	* @return void
	*/
	function enableDescription($a_status)
	{
		$this->description_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getDescriptionStatus()
	{
		return $this->description_enabled;
	}
	
	/**
	* Show hide search result fragments
	*
	* @param bool
	* @return bool
	*/
	function getSearchFragmentStatus()
	{
		return $this->search_fragment_enabled;
	}
	
	/**
	* En/disable description
	*
	* @param bool
	* @return void
	*/
	function enableSearchFragments($a_status)
	{
		$this->search_fragment_enabled = $a_status;

		return;
	}
	
	/**
	 * Enable linked path 
	 * @param bool
	 * @return
	 */
	public function enableLinkedPath($a_status)
	{
		$this->path_linked = $a_status;
	}
	
	/**
	 * enabled relevance 
	 * @return
	 */
	public function enabledRelevance()
	{
		return $this->enabled_relevance;
	}
	
	/**
	 * enable relevance 
	 * @return
	 */
	public function enableRelevance($a_status)
	{
		$this->enabled_relevance = $a_status;	 
	}
	
	/**
	 * set relevance 
	 * @param int
	 * @return
	 */
	public function setRelevance($a_rel)
	{
		$this->relevance = $a_rel; 
	}
	
	/**
	 * get relevance 
	 * @param
	 * @return
	 */
	public function getRelevance()
	{
		return $this->relevance;
	}
	
	/**
	* En/Dis-able icons
	*
	* @param boolean	icons on/off
	*/
	function enableIcon($a_status)
	{
		$this->icons_enabled = $a_status;
	}
	
	/**
	* Are icons enabled?
	*
	* @return boolean	icons enabled?
	*/
	function getIconStatus()
	{
		return $this->icons_enabled;
	}
		
	/**
	* En/Dis-able checkboxes
	*
	* @param boolean	checkbox on/off
	*/
	function enableCheckbox($a_status)
	{
		$this->checkboxes_enabled = $a_status;
	}
	
	/**
	* Are checkboxes enabled?
	*
	* @return boolean	icons enabled?
	*/
	function getCheckboxStatus()
	{
		return $this->checkboxes_enabled;
	}
	
	/**
	* En/Dis-able expand/collapse link
	*
	* @param boolean	checkbox on/off
	*/
	function enableExpand($a_status)
	{
		$this->expand_enabled = $a_status;
	}
	
	/**
	* Is expand/collapse enabled
	*
	* @return boolean	icons enabled?
	*/
	function getExpandStatus()
	{
		return $this->expand_enabled;
	}
	
	function setExpanded($a_status)
	{
		$this->is_expanded = $a_status;
	}
	
	function isExpanded()
	{
		return $this->is_expanded;
	}
	/**
	* Set position input field
	*
	* @param	string		$a_field_index			e.g. "[crs][34]"
	* @param	string		$a_position_value		e.g. "2.0"
	*/
	function setPositionInputField($a_field_index, $a_position_value)
	{
		$this->position_enabled = true;
		$this->position_field_index = $a_field_index;
		$this->position_value = $a_position_value;
	}

	/**
	* En/disable delete
	*
	* @param bool
	* @return void
	*/
	function enableDelete($a_status)
	{
		$this->delete_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getDeleteStatus()
	{
		return $this->delete_enabled;
	}

	/**
	* En/disable cut
	*
	* @param bool
	* @return void
	*/
	function enableCut($a_status)
	{
		$this->cut_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getCutStatus()
	{
		return $this->cut_enabled;
	}
	
	/**
	* En/disable copy
	*
	* @param bool
	* @return void
	*/
	function enableCopy($a_status)
	{
		$this->copy_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getCopyStatus()
	{
		return $this->copy_enabled;
	}

	/**
	* En/disable subscribe
	*
	* @param bool
	* @return void
	*/
	function enableSubscribe($a_status)
	{
		$this->subscribe_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getSubscribeStatus()
	{
		return $this->subscribe_enabled;
	}
	/**
	* En/disable payment
	*
	* @param bool
	* @return void
	*/
	function enablePayment($a_status)
	{
		$this->payment_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getPaymentStatus()
	{
		return $this->payment_enabled;
	}
	/**
	* En/disable link
	*
	* @param bool
	* @return void
	*/
	function enableLink($a_status)
	{
		$this->link_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getLinkStatus()
	{
		return $this->link_enabled;
	}

	/**
	* En/disable path
	*
	* @param bool
	* @return void
	*/
	function enablePath($a_path)
	{
		$this->path_enabled = $a_path;
	}

	/**
	*
	* @param bool
	* @return bool
	*/
	function getPathStatus()
	{
		return $this->path_enabled;
	}
	
	/**
	* En/disable commands
	*
	* @param bool
	* @return void
	*/
	function enableCommands($a_status, $a_std_only = false)
	{
		$this->commands_enabled = $a_status;
		$this->std_cmd_only = $a_std_only;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getCommandsStatus()
	{
		return $this->commands_enabled;
	}

	/**
	* En/disable path
	*
	* @param bool
	* @return void
	*/
	function enableInfoScreen($a_info_screen)
	{
		$this->info_screen_enabled = $a_info_screen;
	}

	/**
	* Add HTML for subitem (used for sessions)
	*
	* @param	string	$a_html		subitems HTML
	*/
	function addSubItemHTML($a_html)
	{
		$this->sub_item_html[] = $a_html;
	}
	
	/**
	*
	* @param bool
	* @return bool
	*/
	function getInfoScreenStatus()
	{
		return $this->info_screen_enabled;
	}
	
	/**
	 * enable progress info
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function enableProgressInfo($a_status)
	{
		$this->progress_enabled = $a_status;
	}
	
	/**
	 * get progress info status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getProgressInfoStatus()
	{
		return $this->progress_enabled;
	}
	
	/**
	 * Enable substitutions
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function enableSubstitutions($a_status)
	{
	 	$this->substitutions_enabled = $a_status;
	}
	
	/**
	 * Get substitution status
	 *
	 * @access public
	 * 
	 */
	public function getSubstitutionStatus()
	{
	 	return $this->substitutions_enabled;
	}
	
	/**
	 * enable item detail links
	 * E.g Direct links to chapters or pages
	 *
	 * @access public
	 * @param bool
	 * @return
	 */
	public function enableItemDetailLinks($a_status)
	{
		$this->item_detail_links_enabled = $a_status;
	}
	
	/**
	 * get item detail link status
	 *
	 * @access public
	 * @return bool
	 */
	public function getItemDetailLinkStatus()
	{
		return $this->item_detail_links_enabled;
	}
	
	/**
	 * set items detail links
	 *
	 * @access public
	 * @param array e.g. array(0 => array('desc' => 'Page: ','link' => 'ilias.php...','name' => 'Page XYZ')
	 * @return
	 */
	public function setItemDetailLinks($a_detail_links,$a_intro_txt = '')
	{
		$this->item_detail_links = $a_detail_links;
		$this->item_detail_links_intro = $a_intro_txt;
	}
	
	/**
	 * insert item detail links
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function insertItemDetailLinks()
	{
		if(!count($this->item_detail_links))
		{
			return true;
		}
		if(strlen($this->item_detail_links_intro))
		{
			$this->tpl->setCurrentBlock('item_detail_intro');
			$this->tpl->setVariable('ITEM_DETAIL_INTRO_TXT',$this->item_detail_links_intro);
			$this->tpl->parseCurrentBlock();			
		}
		
		foreach($this->item_detail_links as $info)
		{
			$this->tpl->setCurrentBlock('item_detail_link');
			$this->tpl->setVariable('ITEM_DETAIL_LINK_TARGET',$info['target']);
			$this->tpl->setVariable('ITEM_DETAIL_LINK_DESC',$info['desc']);
			$this->tpl->setVariable('ITEM_DETAIL_LINK_HREF',$info['link']);
			$this->tpl->setVariable('ITEM_DETAIL_LINK_NAME',$info['name']);
			$this->tpl->parseCurrentBlock();			
		}
		$this->tpl->setCurrentBlock('item_detail_links');
		$this->tpl->parseCurrentBlock();
	}
	
	

	/**
	* @param string title
	* @return bool
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * getTitle overwritten in class.ilObjLinkResourceList.php 
	 *
	 * @return string title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	* @param string description
	* @return bool
	*/
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	/**
	 * getDescription overwritten in class.ilObjLinkResourceList.php 
	 *
	 * @return string description
	 */
	function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * set search fragment 
	 * @param string $a_text highlighted search fragment
	 * @return
	 */
	public function setSearchFragment($a_text)
	{
		$this->search_fragment = $a_text; 
	}
	
	/**
	 * get search fragment
	 * @return
	 */
	public function getSearchFragment()
	{
		return $this->search_fragment;	 
	}
	
	/**
	* Set separate commands
	*
	* @param	boolean	 separate commands
	*/
	function setSeparateCommands($a_val)
	{
		$this->separate_commands = $a_val;
	}
	
	/**
	* Get separate commands
	*
	* @return	boolean	 separate commands
	*/
	function getSeparateCommands()
	{
		return $this->separate_commands;
	}
	/**
	 * get command id
	 * Normally the ref id.
	 * Overwritten for course and category references
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getCommandId()
	{
		return $this->ref_id;
	}
	
	/**
	* Set additional information
	*
	* @param	string		additional information
	*/
	function setAdditionalInformation($a_val)
	{
		$this->additional_information = $a_val;
	}
	
	/**
	* Get additional information
	*
	* @return	string		additional information
	*/
	function getAdditionalInformation()
	{
		return $this->additional_information;
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function checkCommandAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id="")
	{
		global $ilAccess;
		
		if (isset($this->access_cache[$a_permission]["-".$a_cmd][$a_ref_id]))
		{
//echo "A";
			return $this->access_cache[$a_permission]["-".$a_cmd][$a_ref_id];
		}
//echo "<br>Check access in checkCommandAccess";
		$access = $ilAccess->checkAccess($a_permission,$a_cmd,$a_ref_id,$a_type,$a_obj_id);
		if ($ilAccess->getPreventCachingLastResult())
		{
			$this->prevent_access_caching = true;
		}
		$this->access_cache[$a_permission]["-".$a_cmd][$a_ref_id] = $access;
		return $access;
	}
	
	/**
	* inititialize new item (is called by getItemHTML())
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		$this->access_cache = array();
		$this->ref_id = $a_ref_id;
		$this->obj_id = $a_obj_id;
		$this->setTitle($a_title);
		$this->setDescription($a_description);
		#$this->description = $a_description;
				
		// checks, whether any admin commands are included in the output
		$this->adm_commands_included = false;
		$this->prevent_access_caching = false;
	}
	
	/**
	 * Get default command link
	 * Overwritten for e.g categories,courses => they return a goto link
	 * If search engine visibility is enabled these object type return a goto_CLIENT_ID_cat_99.html link
	 *
	 * @access public
	 * @param int command link
	 * 
	 */
	public function createDefaultCommand($command)
	{
		if($this->static_link_enabled)
		{
		 	include_once('classes/class.ilLink.php');
		 	if($link = ilLink::_getStaticLink($this->ref_id,$this->type,false))
		 	{
		 		$command['link'] = $link;
		 		$command['frame'] = '_top';
		 	}
		}
	 	return $command;
	}


	/**
	* Get command link url.
	*
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command link url
	*/
	function getCommandLink($a_cmd)
	{
		// BEGIN WebDAV Get mount webfolder link.
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if ($a_cmd == 'mount_webfolder' && ilDAVActivationChecker::_isActive())
		{
			require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
			$davServer = new ilDAVServer();

			// XXX: The following is a very dirty, ugly trick. 
			//        To mount URI needs to be put into two attributes:
			//        href and folder. This hack returns both attributes
			//        like this:  http://...mount_uri..." folder="http://...folder_uri...
			return $davServer->getMountURI($this->ref_id).
						'" folder="'.$davServer->getFolderURI($this->ref_id);
		}
		// END WebDAV Get mount webfolder link.

		// don't use ctrl here in the moment
		return 'repository.php?ref_id='.$this->getCommandId().'&cmd='.$a_cmd;

		// separate method for this line
		$cmd_link = $this->ctrl->getLinkTargetByClass($this->gui_class_name,
			$a_cmd);
		return $cmd_link;
	}


	/**
	* Get command target frame.
	*
	* Overwrite this method if link frame is not current frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		// BEGIN WebDAV Get mount webfolder link.
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if ($a_cmd == 'mount_webfolder' && ilDAVActivationChecker::_isActive())
		{
			return '_blank';        
		}
		return "";
	}

	/**
	* Get command icon image
	*
	* Overwrite this method if an icon is provided
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		image path
	*/
	function getCommandImage($a_cmd)
	{
		return "";
	}

	/**
	* Get item properties
	*
	* Overwrite this method to add properties at
	* the bottom of the item html
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	public function getProperties($a_item = '')
	{
		$props = array();
		// please list alert properties first
		// example (use $lng->txt instead of "Status"/"Offline" strings):
		// $props[] = array("alert" => true, "property" => "Status", "value" => "Offline");
		// $props[] = array("alert" => false, "property" => ..., "value" => ...);
		// ...

		// BEGIN WebDAV Display locking information
		require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
		if (ilDAVActivationChecker::_isActive())
		{
			require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
			global $ilias, $lng;
			
			// Show lock info
			require_once('Services/WebDAV/classes/class.ilDAVLocks.php');
			$davLocks = new ilDAVLocks();
			if ($ilias->account->getId() != ANONYMOUS_USER_ID)
			{
				$locks =& $davLocks->getLocksOnObjectObj($this->obj_id);
				if (count($locks) > 0)
				{
					$lockUser = new ilObjUser($locks[0]['ilias_owner']);
					
					$props[] = array(
						"alert" => false, 
						"property" => $lng->txt("in_use_by"),
						"value" => $lockUser->getLogin(),
						"link" => 	"./ilias.php?user=".$locks[0]['ilias_owner'].'&cmd=showUserProfile&cmdClass=ilpersonaldesktopgui&cmdNode=1&baseClass=ilPersonalDesktopGUI',
					);
				}
			}
			// END WebDAV Display locking information
			// BEGIN WebDAV Display warning for invisible Unix files and files with special characters
			if (preg_match('/^(\\.|\\.\\.)$/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_special_filename"),
					'propertyNameVisible' => false);
			} 
			else if (preg_match('/^\\./', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_visibility"),
					"value" => $lng->txt("filename_hidden_unix_file"),
					'propertyNameVisible' => false);
			}
			else if (preg_match('/~$/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_visibility"),
					"value" => $lng->txt("filename_hidden_backup_file"),
					'propertyNameVisible' => false);
			}
			else if (preg_match('/[\\/]/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_special_characters"),
					'propertyNameVisible' => false);
			} 
			else if (preg_match('/[\\\\\\/:*?"<>|]/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_windows_special_characters"),
					'propertyNameVisible' => false);
			}
			else if (preg_match('/\\.$/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_windows_empty_extension"),
					'propertyNameVisible' => false);
			} 
			else if (preg_match('/^(\\.|\\.\\.)$/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_special_filename"),
					'propertyNameVisible' => false);
			} 
			else if (preg_match('/#/', $this->title))
			{
				$props[] = array("alert" => true, "property" => $lng->txt("filename_interoperability"),
					"value" => $lng->txt("filename_windows_webdav_issue"),
					'propertyNameVisible' => false);
			}
		}
		// END WebDAV Display warning for invisible files and files with special characters
		// BEGIN ChangeEvent: display changes.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilias, $lng, $ilUser;
			if ($ilias->account->getId() != ANONYMOUS_USER_ID)
			{
				// Performance improvement: for container objects
				// we only display 'changed inside' events, for
				// leaf objects we only display 'object new/changed'
				// events
				$isContainer = in_array($this->type, array('cat','fold','crs','grp'));
				if ($isContainer)
				{
					$state = ilChangeEvent::_lookupInsideChangeState($this->obj_id, $ilUser->getId());
					if ($state > 0)
					{
						$props[] = array("alert" => true, "property" => $lng->txt("event"),
							"value" => $lng->txt('state_changed_inside'),
							'propertyNameVisible' => false);
					}
                                }
                                else
                                {
					$state = ilChangeEvent::_lookupChangeState($this->obj_id, $ilUser->getId());
					if ($state > 0)
					{
						$props[] = array("alert" => true, "property" => $lng->txt("event"),
							"value" => $lng->txt(($state == 1) ? 'state_unread' : 'state_changed'),
							'propertyNameVisible' => false);
					}
				}
			}
		}
		// END ChangeEvent: display changes.

		return $props;
	}
	
	/**
	* add custom property
	*/
	function addCustomProperty($a_property = "", $a_value = "",
		$a_alert = false, $a_newline = false)
	{
		$this->cust_prop[] = array("property" => $a_property, "value" => $a_value,
			"alert" => $a_alert, "newline" => $a_newline);
	}
	
	/**
	* get custom properties
	*/
	function getCustomProperties($a_prop)
	{
		if (is_array($this->cust_prop))
		{
			foreach($this->cust_prop as $prop)
			{
				$a_prop[] = $prop;
			}
		}
		return $a_prop;
	}
	
	/**
	* get notice properties
	*/
	function getNoticeProperties()
	{
		$this->notice_prop = array();
		if($infos = $this->ldap_mapping->getInfoStrings($this->obj_id,true))
		{
			foreach($infos as $info)
			{
				$this->notice_prop[] = array('value' => $info);
			}
		}		
		return $this->notice_prop ? $this->notice_prop : array();
	}	
	/**
	* add a custom command
	*/
	public function addCustomCommand($a_link, $a_lang_var, $a_frame = "")
	{
		$this->cust_commands[] =
			array("link" => $a_link, "lang_var" => $a_lang_var,
			"frame" => $a_frame);
	}


	/**
	* get all current commands for a specific ref id (in the permission
	* context of the current user)
	*
	* !!!NOTE!!!: Please use getListHTML() if you want to display the item
	* including all commands
	*
	* !!!NOTE 2!!!: Please do not overwrite this method in derived
	* classes becaus it will get pretty large and much code will be simply
	* copy-and-pasted. Insert smaller object type related method calls instead.
	* (like getCommandLink() or getCommandFrame())
	*
	* @access	public
	* @param	int		$a_ref_id		ref id of object
	* @return	array	array of command arrays including
	*					"permission" => permission name
	*					"cmd" => command
	*					"link" => command link url
	*					"frame" => command link frame
	*					"lang_var" => language variable of command
	*					"granted" => true/false: command granted or not
	*					"access_info" => access info object (to do: implementation)
	*/
	public function getCommands()
	{
		global $ilAccess, $ilBench;

		$ref_commands = array();
		foreach($this->commands as $command)
		{
			$permission = $command["permission"];
			$cmd = $command["cmd"];
			$lang_var = $command["lang_var"];
			$txt = $command["txt"];

			// BEGIN WebDAV: Suppress commands that don't make sense for anonymous users.
			// Suppress commands that don't make sense for anonymous users
			global $ilias;
			if ($ilias->account->getId() == ANONYMOUS_USER_ID &&
				$command['enable_anonymous'] == 'false')
			{
				continue;
			}
			// END WebDAV: Suppress commands that don't make sense for anonymous users.

			// all access checking should be made within $ilAccess and
			// the checkAccess of the ilObj...Access classes
			$ilBench->start("ilObjectListGUI", "4110_get_commands_check_access");
			//$access = $ilAccess->checkAccess($permission, $cmd, $this->ref_id, $this->type);
			$access = $this->checkCommandAccess($permission,$cmd,$this->ref_id,$this->type);
			$ilBench->stop("ilObjectListGUI", "4110_get_commands_check_access");

			if ($access)
			{
				$cmd_link = $this->getCommandLink($command["cmd"]);
				$cmd_frame = $this->getCommandFrame($command["cmd"]);
				$cmd_image = $this->getCommandImage($command["cmd"]);
				$access_granted = true;
			}
			else
			{
				$access_granted = false;
				$info_object = $ilAccess->getInfo();
			}

			$ref_commands[] = array(
				"permission" => $permission,
				"cmd" => $cmd,
				"link" => $cmd_link,
				"frame" => $cmd_frame,
				"lang_var" => $lang_var,
				"txt" => $txt,
				"granted" => $access_granted,
				"access_info" => $info_object,
				"img" => $cmd_image,
				"default" => $command["default"]
			);
		}

		return $ref_commands;
	}

	// BEGIN WebDAV: Visualize object state in its icon.
	/**
	* Returns the icon image type.
	* For most objects, this is same as the object type, e.g. 'cat','fold'.
	* We can return here other values, to express a specific state of an object,
	* e.g. 'crs_offline", and/or to express a specific kind of object, e.g.
	* 'file_inline'.
	*/
	public function getIconImageType() 
	{
		return $this->type;
	}
	// END WebDAV: Visualize object state in its icon.

	/**
	* insert item title
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	string		$a_title	item title
	*/
	public function insertTitle()
	{
		if (!$this->default_command || !$this->getCommandsStatus())
		{
			$this->tpl->setCurrentBlock("item_title");
			$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			if ($this->default_command["frame"] != "")
			{
				$this->tpl->setCurrentBlock("title_linked_frame");
				$this->tpl->setVariable("TARGET_TITLE_LINKED", $this->default_command["frame"]);
				$this->tpl->parseCurrentBlock();
			}
			
			// workaround for repository frameset
			#var_dump("<pre>",$this->default_command['link'],"</pre>");
			$this->default_command["link"] = 
				$this->appendRepositoryFrameParameter($this->default_command["link"]);
				
			#var_dump("<pre>",$this->default_command['link'],"</pre>");
			

			// the default command is linked with the title
			$this->tpl->setCurrentBlock("item_title_linked");
			$this->tpl->setVariable("TXT_TITLE_LINKED", $this->getTitle());
			$this->tpl->setVariable("HREF_TITLE_LINKED", $this->default_command["link"]);
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->bold_title == true)
		{
			$this->tpl->touchBlock('bold_title_start');
			$this->tpl->touchBlock('bold_title_end');
		}
	}
	
	/**
	 * Insert substitutions 
	 *
	 * @access public
	 * 
	 */
	public function insertSubstitutions()
	{
		$fields_shown = false;
		foreach($this->substitutions->getParsedSubstitutions($this->ref_id,$this->obj_id) as $data)
		{
			if($data['bold'])
			{
				$data['name'] = '<strong>'.$data['name'].'</strong>';
				$data['value'] = '<strong>'.$data['value'].'</strong>';
			}
			$this->tpl->touchBlock("std_prop");
			$this->tpl->setCurrentBlock('item_property');
			if($data['show_field'])
			{
				$this->tpl->setVariable('TXT_PROP',$data['name']);
			}
			$this->tpl->setVariable('VAL_PROP',$data['value']);
			$this->tpl->parseCurrentBlock();

			if($data['newline'])
			{
				$this->tpl->touchBlock('newline_prop');
			}
			$fields_shown = false;
			
		}
		if($fields_shown)
		{
			$this->tpl->touchBlock('newline_prop');
		}
	}


	/**
	* insert item description
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	string		$a_desc		item description
	*/
	function insertDescription()
	{
		if($this->getSubstitutionStatus())
		{
			$this->insertSubstitutions();
			if(!$this->substitutions->isDescriptionEnabled())
			{
				return true;
			}
		}
		
		$this->tpl->setCurrentBlock("item_description");
		$this->tpl->setVariable("TXT_DESC", $this->getDescription());
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	 * Insert highlighted search fragment 
	 * @return
	 */
	public function insertSearchFragment()
	{
		if(strlen($this->getSearchFragment()))
		{
			$this->tpl->setCurrentBlock('search_fragment');
			$this->tpl->setVariable('TXT_SEARCH_FRAGMENT',$this->getSearchFragment().' ...');
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * insert relevance 
	 * @param
	 * @return
	 */
	public function insertRelevance()
	{
		global $lng;
		
		if(!$this->enabledRelevance() or !(int) $this->getRelevance())
		{
			return false;
		}
		
		$width1 = (int) $this->getRelevance();
		$width2 = (int) (100 - $width1);
		
		$this->tpl->setCurrentBlock('relevance');
		#$this->tpl->setVariable('TXT_RELEVANCE',$lng->txt('search_relevance'));
		$this->tpl->setVariable('VAL_REL',sprintf("%.02f %%",$this->getRelevance()));
		$this->tpl->setVariable('WIDTH_A',$width1);
		$this->tpl->setVariable('WIDTH_B',$width2);
		$this->tpl->setVariable('IMG_A',ilUtil::getImagePath("relevance_blue.gif"));
		$this->tpl->setVariable('IMG_B',ilUtil::getImagePath("relevance_dark.gif"));
		$this->tpl->parseCurrentBlock();
		
	}

	/**
	* set output mode
	*
	* @param	string	$a_mode		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* get output mode
	*
	* @return	string		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*/
	function getMode()
	{
		return $this->mode;
	}
	
	/**
	* set depth for precondition output (stops at level 5)
	*/
	function setConditionDepth($a_depth)
	{
		$this->condition_depth = $a_depth;
	}

	/**
	* check current output mode
	*
	* @param	string		$a_mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*
	* @return 	boolen		true if current mode is $a_mode
	*/
	function isMode($a_mode)
	{
		if ($a_mode == $this->mode)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* insert properties
	*
	* @access	private
	*/
	function insertProperties($a_item = '')
	{
		global $ilAccess, $lng;

		$props = $this->getProperties($a_item);
		$props = $this->getCustomProperties($props);

		// add no item access note in public section
		// for items that are visible but not readable
		if ($this->ilias->account->getId() == ANONYMOUS_USER_ID)
		{
			if (!$ilAccess->checkAccess("read", "", $this->ref_id, $this->type, $this->obj_id))
			{
				$props[] = array("alert" => true,
					"value" => $lng->txt("no_access_item_public"),
					"newline" => true);
			}
		}

		$cnt = 1;
		if (is_array($props) && count($props) > 0)
		{
			foreach($props as $prop)
			{
				// BEGIN WebDAV: Display a separator between properties.
				if ($cnt > 1)
				{
					$this->tpl->touchBlock("separator_prop");
				}
				// END WebDAV: Display a separator between properties.

				if ($prop["alert"] == true)
				{
					$this->tpl->touchBlock("alert_prop");
				}
				else
				{
					$this->tpl->touchBlock("std_prop");
				}
				if ($prop["newline"] == true && $cnt > 1)
				{
					$this->tpl->touchBlock("newline_prop");
				}
				//BEGIN WebDAV: Support hidden property names.
				if (isset($prop["property"]) && $prop['propertyNameVisible'] !== false)
				//END WebDAV: Support hidden property names.
				{
					$this->tpl->setCurrentBlock("prop_name");
					$this->tpl->setVariable("TXT_PROP", $prop["property"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("item_property");
				//BEGIN WebDAV: Support links in property values.
				if ($prop['link'])
				{
					$this->tpl->setVariable("LINK_PROP", $prop['link']);
					$this->tpl->setVariable("LINK_VAL_PROP", $prop["value"]);
				}
				else
				{
					$this->tpl->setVariable("VAL_PROP", $prop["value"]);
				}
				//END WebDAV: Support links in property values.
				$this->tpl->parseCurrentBlock();

				$cnt++;
			}
			$this->tpl->setCurrentBlock("item_properties");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function insertNoticeProperties()
	{
		$this->getNoticeProperties();
		foreach($this->notice_prop as $property)
		{
			$this->tpl->setCurrentBlock('notice_item');
			$this->tpl->setVariable('NOTICE_ITEM_VALUE',$property['value']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock('notice_property');
		$this->tpl->parseCurrentBlock();
	}


	/**
	* insert payment information
	*
	* @access	private
	*/
	function insertPayment()
	{
		include_once 'payment/classes/class.ilPaymentObject.php';		

		if((bool)ilGeneralSettings::_getInstance()->get('shop_enabled') &&
		   $this->payment_enabled &&
		   ilPaymentObject::_isBuyable($this->ref_id))
		{	
			if(ilPaymentObject::_hasAccess($this->ref_id))
			{
				$this->ctpl->setCurrentBlock('payment');
				$this->ctpl->setVariable('PAYMENT_TYPE_IMG', ilUtil::getImagePath('icon_pays_access_b.gif'));
				$this->ctpl->setVariable('PAYMENT_ALT_IMG', $this->lng->txt('payment_system') . ': ' . $this->lng->txt('payment_payed_access'));
				$this->ctpl->parseCurrentBlock();				
			}
			else if(ilPaymentObject::_isInCart($this->ref_id))
			{
				$this->ctpl->setCurrentBlock('payment');
				$this->ctpl->setVariable('PAYMENT_TYPE_IMG', ilUtil::getImagePath('icon_pays_cart_b.gif'));
				$this->ctpl->setVariable('PAYMENT_ALT_IMG', $this->lng->txt('payment_system') . ': ' . $this->lng->txt('payment_in_sc'));
				$this->ctpl->parseCurrentBlock();

				$this->insertPaymentCommand();				
			}
			else
			{
				$this->ctpl->setCurrentBlock('payment');
				$this->ctpl->setVariable('PAYMENT_TYPE_IMG', ilUtil::getImagePath('icon_pays_b.gif'));
				$this->ctpl->setVariable('PAYMENT_ALT_IMG', $this->lng->txt('payment_system') . ': ' . $this->lng->txt('payment_buyable'));
				$this->ctpl->parseCurrentBlock();				

				$this->insertPaymentCommand();
			}
		}
	}
	
	protected function insertPaymentCommand()
	{
		include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$this->current_selection_list = new ilAdvancedSelectionListGUI();
		$this->current_selection_list->setListTitle($this->lng->txt("shop_actions"));
		$this->current_selection_list->setId("act_".$this->ref_id);
		//$this->current_selection_list->setSelectionHeaderClass("il_ContainerItemCommand2");
			$this->current_selection_list->setLinksMode("il_ContainerItemCommand2");
			$this->current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$this->current_selection_list->setUseImages(true);
		
		$commands = $this->getCommands($this->ref_id, $this->obj_id);		
		foreach($commands as $command)
		{ 
			if($command['default'] === true)
			{
				$command = $this->createDefaultCommand($command);
				if(is_null($command['link']))
				{
					switch($this->type)
					{
						case 'sahs':
							$command['link'] = 'ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='.$this->ref_id;
							break;
						
						case 'lm':
							$command['link'] = 'ilias.php?baseClass=ilLMPresentationGUI&ref_id='.$this->ref_id;
							break;
						
						default:
							$command['link'] = 'repository.php?ref_id='.$this->ref_id;
							break;	
					}					
				}
				//$this->insertCommand($command['link'].'&purchasetype=demo', $this->lng->txt('payment_demo'), $command['frame']);
				//$this->insertCommand($command['link'].'&purchasetype=buy', $this->lng->txt('buy'), $command['frame']);
				$this->current_selection_list->addItem($this->lng->txt('payment_demo'), "", $command['link'].'&purchasetype=demo', $a_img, $this->lng->txt('payment_demo'), $command['frame']);
				$this->current_selection_list->addItem($this->lng->txt('buy'), "", $command['link'].'&purchasetype=buy', $a_img, $this->lng->txt('buy'), $command['frame']);				
			}
		}
		
		$this->ctrl->clearParametersByClass($this->gui_class_name);
		
		$this->ctpl->setVariable("COMMAND_SELECTION_LIST",
			$this->current_selection_list->getHTML());
			
	
	}

	/**
	* insert all missing preconditions
	*/
	function insertPreconditions()
	{
		global $ilAccess, $lng, $objDefinition;

		include_once("classes/class.ilConditionHandler.php");

		$missing_cond_exist = false;
		
		// do not show multi level conditions (messes up layout)
		if ($this->condition_depth > 0)
		{
			return;
		}
		
		foreach(ilConditionHandler::_getConditionsOfTarget($this->ref_id,$this->obj_id) as $condition)
		{
			if(ilConditionHandler::_checkCondition($condition['id']))
			{
				continue;
			}
			$missing_cond_exist = true;

			$cond_txt = $lng->txt("condition_".$condition["operator"])." ".
				$condition["value"];

			// display trigger item
			$class = $objDefinition->getClassName($condition["trigger_type"]);
			$location = $objDefinition->getLocation($condition["trigger_type"]);
			$full_class = "ilObj".$class."ListGUI";
			include_once($location."/class.".$full_class.".php");
			$item_list_gui = new $full_class($this);
			$item_list_gui->setMode(IL_LIST_AS_TRIGGER);
			$item_list_gui->enablePath(true);
			$item_list_gui->setConditionDepth($this->condition_depth + 1);
			$trigger_html = $item_list_gui->getListItemHTML($condition['trigger_ref_id'],
				$condition['trigger_obj_id'], trim($cond_txt).": ".ilObject::_lookupTitle($condition["trigger_obj_id"]),
				 "");
			$this->tpl->setCurrentBlock("precondition");
			if ($trigger_html == "")
			{
				$trigger_html = $this->lng->txt("precondition_not_accessible");
			}
			//$this->tpl->setVariable("TXT_CONDITION", trim($cond_txt));
			$this->tpl->setVariable("TRIGGER_ITEM", $trigger_html);
			$this->tpl->parseCurrentBlock();
		}

		if ($missing_cond_exist)
		{
			$this->tpl->setCurrentBlock("preconditions");
			$this->tpl->setVariable("TXT_PRECONDITIONS", $lng->txt("preconditions"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* insert command button
	*
	* @access	private
	* @param	string		$a_href		link url target
	* @param	string		$a_text		link text
	* @param	string		$a_frame	link frame target
	*/
	function insertCommand($a_href, $a_text, $a_frame = "", $a_img = "")
	{
		if ($a_frame != "")
		{
			$this->ctpl->setCurrentBlock("item_frame");
			$this->ctpl->setVariable("TARGET_COMMAND", $a_frame);
			$this->ctpl->parseCurrentBlock();
		}

		$this->ctpl->setCurrentBlock("item_command");
		$this->ctpl->setVariable("HREF_COMMAND", $a_href);
		$this->ctpl->setVariable("TXT_COMMAND", $a_text);
		$this->ctpl->parseCurrentBlock();
		
		$this->current_selection_list->addItem($a_text, "", $a_href, $a_img, $a_text, $a_frame);
	}

	/**
	* insert cut command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertDeleteCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}

		if(is_object($this->getContainerObject()) and 
			$this->getContainerObject() instanceof ilAdministrationCommandHandling)
		{
			if($this->checkCommandAccess('delete','delete',$this->ref_id,$this->type))
			{
				$this->ctrl->setParameter($this->getContainerObject(),'item_ref_id',$this->getCommandId());
				$cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "delete");
				$this->insertCommand($cmd_link, $this->lng->txt("delete"));
				$this->adm_commands_included = true;
				return true;
			}
			return false;
		}
		
		if($this->checkCommandAccess('delete','delete',$this->ref_id,$this->type))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "delete");
			$this->insertCommand($cmd_link, $this->lng->txt("delete"), "",
				ilUtil::getImagePath("cmd_delete_s.gif"));
			$this->adm_commands_included = true;
		}
	}

	/**
	* insert link command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertLinkCommand()
	{
		global $ilAccess;

		if ($this->std_cmd_only)
		{
			return;
		}
		// BEGIN PATCH Lucene search
		
		if(is_object($this->getContainerObject()) and 
			$this->getContainerObject() instanceof ilAdministrationCommandHandling)
		{
			global $objDefinition;
	
			if($this->checkCommandAccess('delete','link',$this->ref_id,$this->type) and
				$objDefinition->allowLink(ilObject::_lookupType($this->obj_id)))
			{
				$this->ctrl->setParameter($this->getContainerObject(),'item_ref_id',$this->getCommandId());
				$cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "link");
				$this->insertCommand($cmd_link, $this->lng->txt("link"));
				$this->adm_commands_included = true;
				return true;
			}
			return false;		
		}
		// END PATCH Lucene Search

		// if the permission is changed here, it  has
		// also to be changed in ilContainerGUI, admin command check
		if($this->checkCommandAccess('delete','link',$this->ref_id,$this->type))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "link");
			$this->insertCommand($cmd_link, $this->lng->txt("link"), "",
				ilUtil::getImagePath("cmd_link_s.gif"));
			$this->adm_commands_included = true;
		}
	}

	/**
	* insert cut command
	*
	* @access	protected
	*/
	function insertCutCommand()
	{
		global $ilAccess;
		
		if ($this->std_cmd_only)
		{
			return;
		}
		// BEGIN PATCH Lucene search
		if(is_object($this->getContainerObject()) and 
			$this->getContainerObject() instanceof ilAdministrationCommandHandling)
		{
			if($this->checkCommandAccess('delete','cut',$this->ref_id,$this->type))
			{
				$this->ctrl->setParameter($this->getContainerObject(),'item_ref_id',$this->getCommandId());
				$cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "cut");
				$this->insertCommand($cmd_link, $this->lng->txt("move"));
				$this->adm_commands_included = true;
				return true;
			}
			return false;
		}
		// END PATCH Lucene Search

		// if the permission is changed here, it  has
		// also to be changed in ilContainerGUI, admin command check
		if($this->checkCommandAccess('delete','cut',$this->ref_id,$this->type))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut");
			$this->insertCommand($cmd_link, $this->lng->txt("move"), "",
				ilUtil::getImagePath("cmd_move_s.gif"));
			$this->adm_commands_included = true;
		}
	}
	
	/**
	 * Insert copy command
	 * @return 
	 */
	public function insertCopyCommand()
	{
		if($this->std_cmd_only)
		{
			return;
		}
		
		if($this->checkCommandAccess('copy', 'copy', $this->ref_id, $this->type))
		{
			$this->ctrl->setParameterByClass('ilobjectcopygui','source_id',$this->getCommandId());
			$cmd_copy = $this->ctrl->getLinkTargetByClass('ilobjectcopygui','initTargetSelection');
			$this->insertCommand($cmd_copy, $this->lng->txt('copy'));
			$this->adm_commands_included = true;
			
		}
		return;
	}


	/**
	 * Insert paste command
	 */
	function insertPasteCommand()
	{
		global $ilAccess, $objDefinition;
		
		if ($this->std_cmd_only)
		{
			return;
		}
		
		if(!$objDefinition->isContainer(ilObject::_lookupType($this->obj_id)))
		{
			return false;
		}
		
		if(is_object($this->getContainerObject()) and
			$this->getContainerObject() instanceof ilAdministrationCommandHandling and
			isset($_SESSION['clipboard']))
		{
			$this->ctrl->setParameter($this->getContainerObject(),'item_ref_id',$this->getCommandId());
			$cmd_link = $this->ctrl->getLinkTarget($this->getContainerObject(), "paste");
			$this->insertCommand($cmd_link, $this->lng->txt("paste"));
			$this->adm_commands_included = true;
			return true;
		}
		return false;				
	}

	/**
	* insert subscribe command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertSubscribeCommand()
	{
		global $ilSetting;
		
		if ($this->std_cmd_only)
		{
			return;
		}
		
		if((int)$ilSetting->get('disable_my_offers'))
		{
			return;
		}
		
		$type = ilObject::_lookupType(ilObject::_lookupObjId($this->getCommandId()));

		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
		{
			// BEGIN WebDAV: Lock/Unlock objects
			/* This code section is temporarily commented out. 
			   I will reactivate it at a later point, when I get the
               the backend working properly. - Werner Randelshofer 2008-04-17
			if (is_object($this->container_obj) && $this->rbacsystem->checkAccess("write", $this->ref_id))
			{
				require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
				if (ilDAVServer::_isActive() && ilDAVServer::_isActionsVisible())
				{
					$this->ctrl->setParameter($this->container_obj, "ref_id",
						$this->container_obj->object->getRefId());
					$this->ctrl->setParameter($this->container_obj, "type", $this->type);
					$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
					$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "lock");
					$this->insertCommand($cmd_link, $this->lng->txt("lock"));

					$this->ctrl->setParameter($this->container_obj, "ref_id",
						$this->container_obj->object->getRefId());
					$this->ctrl->setParameter($this->container_obj, "type", $this->type);
					$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
					$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "unlock");
					$this->insertCommand($cmd_link, $this->lng->txt("unlock"));
				}
			}
			*/
			// END WebDAV: Lock/Unlock objects

			if (!$this->ilias->account->isDesktopItem($this->getCommandId(), $type))
			{
				// Pass type and object ID to ilAccess to improve performance
			    global $ilAccess;
    			if ($this->checkCommandAccess("read", "", $this->ref_id, $this->type, $this->obj_id))
				{
					if($this->getContainerObject() instanceof ilDesktopItemHandling)
					{
						$this->ctrl->setParameter($this->container_obj, "type", $type);
						$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
						$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "addToDesk");
						$this->insertCommand($cmd_link, $this->lng->txt("to_desktop"), "",
							ilUtil::getImagePath("cmd_pd_put_s.gif"));
					}					
				}
			}
			else
			{
				if ($this->getContainerObject() instanceof ilDesktopItemHandling)
				{
					$this->ctrl->setParameter($this->container_obj, "type", $type);
					$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->getCommandId());
					$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "removeFromDesk");
					$this->insertCommand($cmd_link, $this->lng->txt("unsubscribe"), "",
						ilUtil::getImagePath("cmd_pd_rem_s.gif"));
				}
			}
		}
	}

	/**
	* insert info screen command
	*
	*/
	function insertInfoScreenCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		$cmd_link = $this->getCommandLink("infoScreen");
		$cmd_frame = $this->getCommandFrame("infoScreen");
		$this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame,
			ilUtil::getImagePath("cmd_info_s.gif"));
	}

	/**
	* insert all commands into html code
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertCommands()
	{
		global $lng;

		if (!$this->getCommandsStatus())
		{
			return;
		}

		$id_ref = ($this->reference_ref_id > 0)
			? $this->reference_ref_id
			: $this->ref_id;
		include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$this->current_selection_list = new ilAdvancedSelectionListGUI();
		$this->current_selection_list->setListTitle($lng->txt("actions"));
		$this->current_selection_list->setId("act_".$id_ref);
		$this->current_selection_list->setSelectionHeaderClass("small");
		$this->current_selection_list->setItemLinkClass("xsmall");
		$this->current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$this->current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$this->current_selection_list->setUseImages(false);
		$this->current_selection_list->setAdditionalToggleElement("lg_div_".$id_ref, "ilContainerListItemOuterHighlight");
		
		include_once './payment/classes/class.ilPaymentObject.php';
		
		$this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", $this->ref_id);

		$commands = $this->getCommands($this->ref_id, $this->obj_id);
		

		$this->default_command = false;
		
		foreach($commands as $command)
		{
			if ($command["granted"] == true )
			{
				if (!$command["default"] === true)
				{
					if (!$this->std_cmd_only)
					{
						// workaround for repository frameset
						$command["link"] = 
							$this->appendRepositoryFrameParameter($command["link"]);
							
						// standard edit icon
						if ($command["lang_var"] == "edit" && $command["img"] == "")
						{
							$command["img"] = ilUtil::getImagePath("cmd_edit_s.gif");
						}
						
						$cmd_link = $command["link"];
						$txt = ($command["lang_var"] == "")
							? $command["txt"]
							: $this->lng->txt($command["lang_var"]);
						$this->insertCommand($cmd_link, $txt,
							$command["frame"], $command["img"]);
					}
				}
				else
				{
					$this->default_command = $this->createDefaultCommand($command);
					//$this->default_command = $command;
				}
			}
			elseif($command["default"] === true)
			{
				$items =& $command["access_info"];
				foreach ($items as $item)
				{
					if ($item["type"] == IL_NO_LICENSE)
					{
						$this->addCustomProperty($this->lng->txt("license"),$item["text"],true);
						$this->enableProperties(true);
						break;
					}
				}
			}
		}

		// custom commands
		if (is_array($this->cust_commands))
		{
			foreach ($this->cust_commands as $command)
			{
				$this->insertCommand($command["link"], $this->lng->txt($command["lang_var"]),
					$command["frame"]);
			}
		}

		// info screen commmand
		if ($this->getInfoScreenStatus())
		{
			$this->insertInfoScreenCommand();
		}

		if (!$this->isMode(IL_LIST_AS_TRIGGER))
		{
			// delete
			if ($this->delete_enabled)
			{
				$this->insertDeleteCommand();
			}

			// link
			if ($this->link_enabled)
			{
				$this->insertLinkCommand();
			}

			// cut
			if ($this->cut_enabled)
			{
				$this->insertCutCommand();
			}

			// copy
			if ($this->copy_enabled)
			{
				$this->insertCopyCommand();
			}

			// subscribe
			if ($this->subscribe_enabled)
			{
				$this->insertSubscribeCommand();
			}

			// BEGIN PATCH Lucene search
			if($this->cut_enabled or $this->link_enabled)
			{
				$this->insertPasteCommand();
			}
			// END PATCH Lucene Search

		}
		$this->ctrl->clearParametersByClass($this->gui_class_name);

		$this->ctpl->setVariable("COMMAND_SELECTION_LIST",
			$this->current_selection_list->getHTML());
	}

	/**
	* workaround: all links into the repository (from outside)
	* must tell repository to setup the frameset
	*/
	function appendRepositoryFrameParameter($a_link)
	{
		$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

		if (substr($script,0,14) != "repository.php" &&
			is_int(strpos($a_link,"repository.php")))
		{
			if ($this->type != "frm")
			{
				$a_link = 
					ilUtil::appendUrlParameterString($a_link, "rep_frame=1");
			}
		}
		
		return $a_link;
	}

	/**
	* insert path
	*/
	function insertPath()
	{
		global $tree, $lng;
		
		if($this->getPathStatus() != false)
		{
			include_once './Services/Tree/classes/class.ilPathGUI.php';
			$path_gui = new ilPathGUI();
			$path_gui->enableTextOnly(!$this->path_linked);
			$path_gui->setUseImages(false);
				
			$this->tpl->setCurrentBlock("path_item");
			$this->tpl->setVariable('PATH_ITEM',$path_gui->getPath(ROOT_FOLDER_ID,$this->ref_id));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("path");
			$this->tpl->setVariable("TXT_LOCATION", $lng->txt("locator"));
			$this->tpl->parseCurrentBlock();
			return true;
		}
	}
	
	/**
	 * insert progress info
	 *
	 * @access public
	 * @return
	 */
	public function insertProgressInfo()
	{
		return true;
	}
	
	
	/**
	* Insert icons and checkboxes
	*/
	function insertIconsAndCheckboxes()
	{
		global $lng, $objDefinition;
		
		$cnt = 0;
		if ($this->getCheckboxStatus())
		{
			$this->tpl->setCurrentBlock("check");
			$this->tpl->setVariable("VAL_ID", $this->getCommandId());
			$this->tpl->parseCurrentBlock();
			$cnt += 1;
		}
		elseif($this->getExpandStatus())
		{
			$this->tpl->setCurrentBlock('expand');
			
			if($this->isExpanded())
			{
				$this->ctrl->setParameter($this->container_obj,'expand',-1 * $this->obj_id);
				$this->tpl->setVariable('EXP_HREF',$this->ctrl->getLinkTarget($this->container_obj));
				$this->ctrl->clearParameters($this->container_obj);			
				$this->tpl->setVariable('EXP_IMG',ilUtil::getImagePath('browser/minus.gif'));				
			$this->tpl->setVariable('EXP_ALT',$this->lng->txt('collapse'));
			}
			else
			{
				$this->ctrl->setParameter($this->container_obj,'expand',$this->obj_id);
				$this->tpl->setVariable('EXP_HREF',$this->ctrl->getLinkTarget($this->container_obj));
				$this->ctrl->clearParameters($this->container_obj);
				$this->tpl->setVariable('EXP_IMG',ilUtil::getImagePath('browser/plus.gif'));
				$this->tpl->setVariable('EXP_ALT',$this->lng->txt('expand'));
			}
			
			$this->tpl->parseCurrentBlock();
			$cnt += 1;
		}
		
		if ($this->getIconStatus())
		{
			if ($cnt == 1)
			{
				$this->tpl->touchBlock("i_1");	// indent
			}
			$this->tpl->setCurrentBlock("icon");
			if (!$objDefinition->isPlugin($this->getIconImageType()))
			{
				$this->tpl->setVariable("ALT_ICON", $lng->txt("icon")." ".$lng->txt("obj_".$this->getIconImageType()));
			}
			else
			{
				include_once("./Services/Component/classes/class.ilPlugin.php");
				$this->tpl->setVariable("ALT_ICON", $lng->txt("icon")." ".
					ilPlugin::lookupTxt("rep_robj", $this->getIconImageType(), "obj_".$this->getIconImageType()));
			}

			$this->tpl->setVariable("SRC_ICON",
				ilObject::_getIcon($this->obj_id, "small", $this->getIconImageType()));
			$this->tpl->parseCurrentBlock();
			$cnt += 1;
		}
		
		$this->tpl->touchBlock("d_".$cnt);	// indent main div
	}
	
	/**
	* Insert subitems
	*/
	function insertSubItems()
	{
		foreach ($this->sub_item_html as $sub_html)
		{
			$this->tpl->setCurrentBlock("subitem");
			$this->tpl->setVariable("SUBITEM", $sub_html);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* Insert field for positioning
	*/
	function insertPositionField()
	{
		if ($this->position_enabled)
		{
			$this->tpl->setCurrentBlock("position");
			$this->tpl->setVariable("POS_ID", $this->position_field_index);
			$this->tpl->setVariable("POS_VAL", $this->position_value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* returns whether any admin commands (link, delete, cut)
	* are included in the output
	*/
	function adminCommandsIncluded()
	{
		return $this->adm_commands_included;
	}

	/**
	 * Store access cache
	 */
	function storeAccessCache()
	{
		global $ilUser;
		if($this->acache->getLastAccessStatus() == "miss" &&
			!$this->prevent_access_caching)
		{
			$this->acache->storeEntry($ilUser->getId().":".$this->ref_id,
				serialize($this->access_cache), $this->ref_id);
		}
	}
	
	/**
	* Get all item information (title, commands, description) in HTML
	*
	* @access	public
	* @param	int			$a_ref_id		item reference id
	* @param	int			$a_obj_id		item object id
	* @param	int			$a_title		item title
	* @param	int			$a_description	item description
	* @return	string		html code
	*/
	function getListItemHTML($a_ref_id, $a_obj_id, $a_title, $a_description)
	{

		global $ilAccess, $ilBench, $ilUser;

		// this variable stores wheter any admin commands
		// are included in the output
		$this->adm_commands_included = false;

		// only for permformance exploration
		$type = ilObject::_lookupType($a_obj_id);

		// initialization
		$ilBench->start("ilObjectListGUI", "1000_getListHTML_init$type");
		$this->tpl =& new ilTemplate ("tpl.container_list_item.html", true, true);
		$this->ctpl =& new ilTemplate ("tpl.container_list_item_commands.html", true, true);
		$this->initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
		$ilBench->stop("ilObjectListGUI", "1000_getListHTML_init$type");

		// read from cache
		include_once("./Services/Object/classes/class.ilListItemAccessCache.php");
		$this->acache = new ilListItemAccessCache();
		$cres = $this->acache->getEntry($ilUser->getId().":".$a_ref_id);
		if($this->acache->getLastAccessStatus() == "hit")
		{
			$this->access_cache = unserialize($cres);
		}

  		// visible check
		$ilBench->start("ilObjectListGUI", "2000_getListHTML_check_visible");
		if (!$this->checkCommandAccess("visible", "", $a_ref_id, "", $a_obj_id))
		{
			$this->storeAccessCache();
			$ilBench->stop("ilObjectListGUI", "2000_getListHTML_check_visible");
			return "";
		}
		$ilBench->stop("ilObjectListGUI", "2000_getListHTML_check_visible");


// commands
		$ilBench->start("ilObjectListGUI", "4000_insert_commands");
		$this->insertCommands();
		$ilBench->stop("ilObjectListGUI", "4000_insert_commands");

		// write to cache
		$this->storeAccessCache();

		// payment
		$ilBench->start("ilObjectListGUI", "5000_insert_pay");
		$this->insertPayment();
		$ilBench->stop("ilObjectListGUI", "5000_insert_pay");
		
		if($this->getProgressInfoStatus())
		{
			$this->insertProgressInfo();	
		}

		if ($this->getCommandsStatus() || 
			($this->payment_enabled && (bool)ilGeneralSettings::_getInstance()->get('shop_enabled')))
		{
			if (!$this->getSeparateCommands())
			{
				$this->tpl->setVariable("COMMANDS", $this->ctpl->get());
			}
		}

		// insert title and describtion
		$ilBench->start("ilObjectListGUI", "3000_insert_title_desc");
		$this->insertTitle();
		if (!$this->isMode(IL_LIST_AS_TRIGGER))
		{
			if ($this->getDescriptionStatus())
			{
				$this->insertDescription();
			}
		}
		$ilBench->stop("ilObjectListGUI", "3000_insert_title_desc");

		if($this->getSearchFragmentStatus())
		{
			$this->insertSearchFragment();
		}
		if($this->enabledRelevance())
		{
			$this->insertRelevance();
		}

		// properties
		$ilBench->start("ilObjectListGUI", "6000_insert_properties$type");
		if ($this->getPropertiesStatus())
		{
			$this->insertProperties();
		}
		$ilBench->stop("ilObjectListGUI", "6000_insert_properties$type");

		// notice properties
		$ilBench->start("ilObjectListGUI", "6500_insert_notice_properties$type");
		if($this->getNoticePropertiesStatus())
		{
			$this->insertNoticeProperties();
		}
		$ilBench->stop("ilObjectListGUI", "6500_insert_notice_properties$type");

		// preconditions
		$ilBench->start("ilObjectListGUI", "7000_insert_preconditions");
		if ($this->getPreconditionsStatus())
		{
			$this->insertPreconditions();
		}
		$ilBench->stop("ilObjectListGUI", "7000_insert_preconditions");

		// path
		$ilBench->start("ilObjectListGUI", "8000_insert_path");
		$this->insertPath();
		$ilBench->stop("ilObjectListGUI", "8000_insert_path");
		
		$ilBench->start("ilObjectListGUI", "8500_item_detail_links");
		if($this->getItemDetailLinkStatus())
		{
			$this->insertItemDetailLinks();			
		}
		$ilBench->stop("ilObjectListGUI", "8500_item_detail_links");

		// icons and checkboxes
		$this->insertIconsAndCheckboxes();
		
		// input field for position
		$this->insertPositionField();

		// subitems
		$this->insertSubItems();

		// reset properties and commands
		$this->cust_prop = array();
		$this->cust_commands = array();
		$this->sub_item_html = array();
		$this->position_enabled = false;

		$id_ref = ($this->reference_ref_id > 0)
			? $this->reference_ref_id
			: $this->ref_id;

		$this->tpl->setVariable("DIV_ID", 'id = "lg_div_'.$id_ref.'"');
		$this->tpl->setVariable("ADDITIONAL", $this->getAdditionalInformation());
		
		return $this->tpl->get();
	}
	
	/**
	* Get commands HTML (must be called after get list item html)
	*/
	function getCommandsHTML()
	{
		return $this->ctpl->get();
	}
	
	/**
	* Returns whether current item is a block in a side column or not
	*/
	function isSideBlock()
	{
		return false;
	}

	/**
	* 
	* @access	public
	* @params	boolean	$a_bold_title	set the item title bold
	*/
	public function setBoldTitle($a_bold_title)
	{
		$this->bold_title = $a_bold_title;
		
	}
	
	/**
	* 
	* @access	public
	* @return	boolean	returns if the item title is bold or not
	*/
	public function isTitleBold()
	{
		return $this->bold_title;
	}
	
	
} // END class.ilObjectListGUI
?>
