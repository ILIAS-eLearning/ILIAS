<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopLinkBuilder
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*
*
*/

class ilShopLinkBuilder
{
	// define reachable shop-targets for goto_ links
	static public $linkArray = array(
		'ilshopgui' =>  array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopGUI'),
		'ilshopadvancedsearchgui' =>  array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopAdvancedSearchGUI'),
		'ilshopinfogui' =>  array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopInfoGUI'),
		'ilshopnewsgui' =>  array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopNewsGUI'),
		'ilshopboughtobjectsgui' => array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopBoughtObjectsGUI'),
		'ilshopshoppingcartgui' => array('baseClass' => 'ilShopController', 'cmdClass' => 'ilShopShoppingCartGUI'),
		'iltermsconditionsgui' => array('baseClass' => 'ilShopController', 'cmdClass' => 'ilTermsConditionsGUI')
	);

	/**
	 *
	 * @param ilSetting $settings
	 */

	public function __construct()
	{
		global $ilSetting;
		$this->settings = $ilSetting;
	}

	public function buildLink($key)
	{

		$link = ILIAS_HTTP_PATH.'/goto_'.CLIENT_ID.'_'
			.strtolower(self::$linkArray[strtolower($key)]['cmdClass']).'_1.html';

		return $link;

		/*  # goto links also work if open_google == false
		if ($this->settings->get('open_google') == true )
		{
			
			$link = ILIAS_HTTP_PATH.'/goto_'.CLIENT_ID.'_'.$key.'_1.html';
			return $link;
		} 
		else
		{
		
			$link = ILIAS_HTTP_PATH.'/ilias.php?baseClass='.self::$linkArray[strtolower($key)]['baseClass']
					.'&cmdClass='.self::$linkArray[strtolower($key)]['cmdClass'];

			return $link;
		}*/
	}
}
?>
