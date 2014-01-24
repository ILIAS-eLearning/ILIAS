<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/COPage/classes/class.ilPageContent.php';

/**
* Class ilPCLoginPageElement
*
* Login page element object (see ILIAS DTD). Inserts login page elements
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCLoginPageElement extends ilPageContent
{
	public $res_node;
	
	private static $types = array(
		'login-form' => 'login_form',
		'cas-login-form' => 'cas_login_form',
		'shibboleth-login-form' => 'shib_login_form',
		'openid-login-form' => 'openid_login_form',
		'registration-link' => 'registration_link',
		'language-selection' => 'language_selection',
		'user-agreement' => 'user_agreement_link'
	);

	/**
	 * Get all types
	 * @return array all type
	 */
	public static function getAllTypes()
	{
		return self::$types;
	}

	/**
	* Init page content component.
	*/
	public function init()
	{
		$this->setType('lpe');
	}

	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);						// this is the PageContent node
		$this->res_node = $a_node->first_child();		// this is the login page element
	}

	/**
	* Create resources node in xml.
	*
	* @param	object	$a_pg_obj		Page Object
	* @param	string	$a_hier_id		Hierarchical ID
	*/
	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$lpe = $this->dom->create_element('LoginPageElement');
		$this->res_node = $this->node->append_child($lpe);
	}

	/**
	* Set Type of Login Page Element
	*
	* @param	string	$a_type		Resource Type Group
	*/
	function setLoginPageElementType($a_type)
	{
		if (!empty($a_type))
		{
			$this->res_node->set_attribute('Type',$a_type);
		}
	}

	/**
	* Get log page element type
	*
	* @return	string		resource type group
	*/
	function getLoginPageElementType()
	{
		if (is_object($this->res_node))
		{
			return $this->res_node->get_attribute('Type');
		}
	}

	/**
	 * set alignment
	 */
	public function setAlignment($a_alignment)
	{
		$this->res_node->set_attribute('HorizontalAlign',$a_alignment);
	}

	/**
	 * Get alignment
	 * @return string $alignment
	 */
	public function getAlignment()
	{
		if(is_object($this->res_node))
		{
			return $this->res_node->get_attribute('HorizontalAlign');
		}
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_login_page_element");
	}

}
?>