<?php

require_once "./Modules/Bibliographic/classes/class.ilBibliographicEntry.php";
require_once "./Modules/Bibliographic/classes/Admin/class.ilBibliographicSetting.php";
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once('./Modules/Bibliographic/classes/Types/class.ilBibTex.php');
require_once('./Modules/Bibliographic/classes/Types/class.ilRis.php');

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 *
 * @ilCtrl_Calls ilObjBibliographicDetailsGUI: ilBibliographicGUI
 */
class ilBibliographicDetailsGUI {

	/**
	 * @var ilObjBibliographic
	 */
	public $bibl_obj;
	/**
	 * @var ilBibliographicEntry
	 */
	public $entry;


	/**
	 * @param ilObjBibliographic $bibl_obj
	 * @param                    $entry_id
	 *
	 * @return ilBibliographicDetailsGUI
	 */
	public static function getInstance(ilObjBibliographic $bibl_obj, $entry_id) {
		$obj = new self();
		$obj->bibl_obj = $bibl_obj;
		$obj->entry = ilBibliographicEntry::getInstance($obj->bibl_obj->getFiletype(), $entry_id);

		return $obj;
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		global $tpl, $ilTabs, $ilCtrl, $lng;

		$form = new ilPropertyFormGUI();
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, 'showContent'));
		$form->setTitle($lng->txt('detail_view'));
		// add link button if a link is defined in the settings
		$set = new ilSetting("bibl");
		$link = $set->get(strtolower($this->bibl_obj->getFiletype()));
		if (!empty($link)) {
			$form->addCommandButton('autoLink', 'Link');
		}

		$attributes = $this->entry->getAttributes();
		//translate array key in order to sort by those keys
		foreach ($attributes as $key => $attribute) {
			//Check if there is a specific language entry
			if ($lng->exists($key)) {
				$strDescTranslated = $lng->txt($key);
			} //If not: get the default language entry
			else {
				$arrKey = explode("_", $key);
				$is_standard_field = false;
				switch ($arrKey[0]) {
					case 'bib':
						$is_standard_field = ilBibTex::isStandardField($arrKey[2]);
						break;
					case 'ris':
						$is_standard_field = ilRis::isStandardField($arrKey[2]);
						break;
				}
				//				var_dump($is_standard_field); // FSX
				if ($is_standard_field) {
					$strDescTranslated = $lng->txt($arrKey[0] . "_default_" . $arrKey[2]);
				} else {
					$strDescTranslated = $arrKey[2];
				}
			}
			unset($attributes[$key]);
			$attributes[$strDescTranslated] = $attribute;
		}
		// sort attributes alphabetically by their array-key
		ksort($attributes, SORT_STRING);
		// render attributes to html
		foreach ($attributes as $key => $attribute) {
			$ci = new ilCustomInputGUI($key);
			$ci->setHtml($attribute);
			$form->addItem($ci);
		}
		// generate/render links to libraries
		$settings = ilBibliographicSetting::getAll();
		foreach ($settings as $set) {
			$ci = new ilCustomInputGUI($set->getName());
			$ci->setHtml($set->getButton($this->bibl_obj, $this->entry));
			$form->addItem($ci);
		}
		$tpl->setPermanentLink("bibl", $this->bibl_obj->getRefId(), "_" . $_GET[ilObjBibliographicGUI::P_ENTRY_ID]);

		// set content and title
		return $form->getHTML();
		//Permanent Link
	}
	/**
	 * generate URL to library
	 *
	 * public function generateLibraryLink($base_url){
	 * global $ilDB, $ilCtrl;
	 *
	 * // get the link/logic from Settings
	 * $bibl_settings = new ilSetting("bibl");
	 *
	 * // get entry's and settings' attributes
	 * $entry_attributes = $this->entry->getAttributes();
	 * $attr_order = explode(",", $bibl_settings->get(strtolower($this->bibl_obj->getFiletype())."_ord"));
	 *
	 * if($attr_order[0] == "" && sizeof($attr_order) == 1){
	 * // set default
	 * switch($this->bibl_obj->getFiletype()){
	 * case 'bib':
	 * $attr_order = array("isbn", "issn", "title");
	 * break;
	 * case 'ris':
	 * $attr_order = array("sn","ti");
	 * break;
	 * default:
	 * $attr_order = array("isbn");
	 * }
	 *
	 * }
	 *
	 * switch($this->bibl_obj->getFiletype()){
	 * case 'bib':
	 * $prefix = "bib_default_";
	 * break;
	 * case 'ris':
	 * $prefix = "ris_default_";
	 * break;
	 * }
	 *
	 * // get first existing attribute (in order of the settings or default if nothing set)
	 * $i = 0;
	 * while(empty($entry_attributes[$prefix.trim(strtolower($attr_order[$i]))]) && ($i < 10)){
	 * $i++;
	 * }
	 * $attr = trim(strtolower($attr_order[$i]));
	 * $value = $entry_attributes[$prefix.$attr];
	 *
	 * switch($attr){
	 * case 'ti':
	 * $attr="title";
	 * break;
	 * case 'sn':
	 * if(strlen($value)<=9){
	 * $attr="issn";
	 * }else{
	 * $attr="isbn";
	 * }
	 * break;
	 * case 'pb':
	 * $attr="publisher";
	 * break;
	 * default:
	 * }
	 *
	 *
	 * // generate and return full link
	 * $full_link = $base_url."?".$attr."=".$value;
	 * return $full_link;
	 *
	 * }*/
}

?>