<?php

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/*
 * Abstract base class for course, group participants table guis
 * @author Stefan Meyer <smeyer.ilias@gmx.de
 */
abstract class ilParticipantTableGUI extends ilTable2GUI
{
	protected static $export_allowed = false;
	protected static $confirmation_required = true;
	protected static $accepted_ids = null;
	protected static $all_columns = null;
	protected static $has_odf_definitions = false;

	/**
	 * Get selectable columns
	 * @return 
	 */
	public function getSelectableColumns()
	{		
		global $ilSetting;
		
		$GLOBALS['lng']->loadLanguageModule('ps');
		if(self::$all_columns)
		{
			# return self::$all_columns;
		}

		include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
		$ef = ilExportFieldsInfo::_getInstanceByType($this->getParentObject()->object->getType());
		self::$all_columns = $ef->getSelectableFieldsInfo($this->getParentObject()->object->getId());
		
		if ($this->type == 'member' &&
			$ilSetting->get('user_portfolios'))
		{
			self::$all_columns['prtf'] = array(
				'txt' => $this->lng->txt('obj_prtf'),
				'default' => false
			);			
		}
		
		return self::$all_columns;
	}

	/**
	 * Check acceptance
	 * @param object $a_usr_id
	 * @return 
	 */
    public function checkAcceptance($a_usr_id)
    {
        if(!self::$confirmation_required)
        {
            return true;
        }
        if(!self::$export_allowed)
        {
            return false;
        }
        return in_array($a_usr_id,self::$accepted_ids);
    }

	/**
     * Init acceptance
     * @return 
     */
    protected function initSettings()
    {
        if(self::$accepted_ids !== NULL)
        {
            return true;
        }
        self::$export_allowed = ilPrivacySettings::_getInstance()->checkExportAccess($this->getParentObject()->object->getRefId());
        self::$confirmation_required = ilPrivacySettings::_getInstance()->groupConfirmationRequired();
		
        include_once 'Services/Membership/classes/class.ilMemberAgreement.php';
        self::$accepted_ids = ilMemberAgreement::lookupAcceptedAgreements($this->getParentObject()->object->getId());

		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		self::$has_odf_definitions = ilCourseDefinedFieldDefinition::_hasFields($this->getParentObject()->object->getId());
    }

	/**
	 * show edit links
	 * @param type $a_set
	 * @return boolean
	 */
	protected function showActionLinks($a_set)
	{
		if(!$this->show_edit_link)
		{
			return true;
		}
		
		if(!self::$has_odf_definitions)
		{
			$this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);
			$this->tpl->setCurrentBlock('link');
			$this->tpl->setVariable('LINK_NAME', $this->ctrl->getLinkTarget($this->parent_obj, 'editMember'));
			$this->tpl->setVariable('LINK_TXT', $this->lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
			return true;
		}
		
		// show action menu
		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setSelectionHeaderClass('small');
		$list->setItemLinkClass('small');
		$list->setId('actl_'.$a_set['usr_id'].'_'.$this->getId());
		$list->setListTitle($this->lng->txt('actions'));

		$this->ctrl->setParameter($this->parent_obj, 'member_id', $a_set['usr_id']);
		$list->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTarget($this->getParentObject(),'editMember'));
		
		$this->ctrl->setParameterByClass('ilobjectcustomuserfieldsgui','member_id',$a_set['usr_id']);
		
		$trans = $this->lng->txt($this->getParentObject()->object->getType().'_cdf_edit_member');
		$list->addItem($trans, '', $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui','editMember'));
		
		
		$this->tpl->setVariable('ACTION_USER',$list->getHTML());
		
	}
	
}
?>
