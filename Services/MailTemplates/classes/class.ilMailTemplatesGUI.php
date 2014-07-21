<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';

/**
 * @ilCtrl_isCalledBy ilMailTemplatesGUI: ilObjMailGUI
 */
class ilMailTemplatesGUI extends ilObjectGUI
{
	/**
	 * @var ilLanguage
	 */
	public $lng;

	/**
	 * @var ilTemplate
	 */
	public $tpl;

	/**
	 * @var ilDB
	 */
	public $ilDB;

	/**
	 * @var ilCtrl
	 */
	public $ilCtrl;
	
	/**
	 * Constructor
	 * @access public
	 */
	public function __construct($a_data,$a_id,$a_call_by_reference)
	{
		/**
		 * @var           $ilCtrl ilCtrl
		 * @var           $lng    ilLanguage
		 * @var           $ilDB   ilDB
		 * @var           $tpl    ilTemplate
		 */
		global $lng, $tpl, $ilDB, $ilCtrl, $ilSetting;

		$this->lng       = $lng;
		$this->tpl       = $tpl;
		$this->ilDB      = $ilDB;
		$this->ilCtrl    = $ilCtrl;

		$this->type = 'mail';
		parent::__construct($a_data,$a_id,$a_call_by_reference, false);

		$this->lng->loadLanguageModule('mail');
	}

	/**
	 * @return bool
	 * @throws ilException
	 */
	public function executeCommand()
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;
		
		$next_class = $this->ilCtrl->getNextClass($this);

		if($next_class != '')
		{
			switch($next_class)
			{
				default:
					throw new ilException('No such class to forward in '.__CLASS__.', given: '.$next_class);
			}
		} 
		else 
		{
			if(!$rbacsystem->checkAccess('visible,write', $_GET['ref_id']))
			{
				$this->ilias->raiseError($this->lng->txt('msg_no_perm_write'), $this->ilias->error_obj->WARNING);
			}

			$cmd = $this->ilCtrl->getCmd();
			
			if(strlen($cmd) == 0 && isset($_POST['cmd']))
			{
				reset($_POST['cmd']);
				$cmd = key($_POST['cmd']);
			}
			
			switch($cmd)
			{
				case 'cancel_template_type_settings':
				case 'view_template_types':
					$this->showTemplateTypesTable();
					break;
				
				case 'show_template_type_settings':
					$this->showTemplateTypeSettings();
					break;
				
				case 'save_template_type_settings':
				case 'edit_template_type_settings':
					$this->handleEditTemplateTypeSettings();
					break;
				
				case 'new_template_type':
					$this->handleNewTemplateTypeSettings();
					break;
				
				case 'confirm_delete_template_type':
					$this->showDeleteTemplateTypeConfirmation();
					break;

				case 'show_template_variants':
					// This method should show a table gui with language variants. In the first version,
					// we will pretend that there is only one variant, which is for the default language of the
					// installation. In order to hook in the multi-language part, here is the plance to start.
					/**
					 * @var $ilSetting ilSetting
					 */
					global $ilSetting;
					$install_default_language = $ilSetting->get('language', 'en');
					$this->showTemplateVariantByLanguage((int)$_GET['template_id'], $install_default_language);
					break;
				
				case 'edit_template_variant':
					$this->handleEditTemplateVariant();
					break;
				
				case 'delete_template_type':
					$this->handleDeleteTemplateType();
					break;

				case 'apply_template_type_filter':
					$this->applyTemplateTypeFilter();
					break;

				case 'reset_template_type_filter':
					$this->resetTemplateTypeFilter();
					break;

				case 'show_frame_settings':
					$this->showFrameSettings();
					break;

				case 'save_frame_settings':
					$this->saveFrameSettings();
					break;
				
				
				default:
					throw new Exception('No such command in '.__CLASS__.', given: '.$cmd);
			}
		}
		$this->tabs_gui->activateTab('templates');
		$this->renderSubTabs($cmd);

		return true;
	}

	/**
	 * @param string $cmd
	 */
	protected function renderSubTabs($cmd)
	{
		$this->tabs_gui->addSubTab('templates', $this->lng->txt('templates'), $this->ctrl->getLinkTarget($this, 'view_template_types'));
		$this->tabs_gui->addSubTab('frame', $this->lng->txt('mail_template_frame'), $this->ctrl->getLinkTarget($this, 'show_frame_settings'));
		
		if(in_array($cmd, array('show_frame_settings', 'save_frame_settings')))
		{
			$this->tabs_gui->activateSubTab('frame');
		}
		else
		{
			$this->tabs_gui->activateSubTab('templates');
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function showFrameSettings(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			require_once 'Services/MailTemplates/classes/class.ilMailTemplateFrameForm.php';
			$frame_form = new ilMailTemplateFrameForm($this->lng, $this->ilCtrl);

			require_once 'Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php';
			$entity = new ilMailTemplateFrameSettingsEntity($this->ilDB, new ilSetting('mail_tpl'));
			$form = $frame_form->getPopulatedForm($entity);
		}
		
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * 
	 */
	protected function saveFrameSettings()
	{
		require_once 'Services/MailTemplates/classes/class.ilMailTemplateFrameForm.php';
		$frame_form_container = new ilMailTemplateFrameForm($this->lng, $this->ctrl);
		if($frame_form_container->getForm()->checkInput())
		{
			require_once 'Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php';
			$entity = new ilMailTemplateFrameSettingsEntity($this->ilDB, new ilSetting('mail_tpl'));
			$entity->setPlainTextFrame($frame_form_container->getForm()->getInput('mail_template_frame_plain'));
			$entity->setHtmlFrame($frame_form_container->getForm()->getInput('mail_message_html'));
			$entity->setImageStyles($frame_form_container->getForm()->getInput('mail_template_footer_image_attributes'));

			$file_data = $frame_form_container->getForm()->getInput('mail_template_footer_image');
			if(
				isset($_POST['mail_template_footer_image_delete']) &&
				(int)$_POST['mail_template_footer_image_delete']
			)
			{
				$entity->deleteImage();
			}
			else if(is_array($file_data) && isset($file_data['tmp_name']) && isset($file_data['name']) && strlen($file_data['tmp_name']))
			{
				$entity->uploadImage($file_data['tmp_name'], $file_data['name']);
			}

			$entity->save();
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
			$this->ilCtrl->redirect($this, 'show_frame_settings');
		}

		$frame_form_container->getForm()->setValuesByPost();
		$this->showFrameSettings($frame_form_container->getForm());
	}
	
	/**
	 * 
	 */
	protected function showTemplateTypesTable()
	{
		require_once 'Services/MailTemplates/classes/class.ilMailTemplatesTableGUI.php';
		$table_gui = new ilMailTemplatesTableGUI($this, 'view_template_types');
		$table_gui->fetchItems();
		$this->tpl->setContent($table_gui->getHTML());
	}

	/**
	 * 
	 */
	protected function showTemplateTypeSettings()
	{
		if(!isset($_GET['template_id']) || !(int)$_GET['template_id'])
		{
			$this->showTemplateTypesTable();
			return;
		}
		
		require_once 'Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		$settings_entity = new ilMailTemplateSettingsEntity();
		$settings_entity->setIlDB($this->ilDB);
		$settings_entity->loadById((int)$_GET['template_id']);

		// gev-patch start
		//require_once 'Services/MailTemplates/classes/class.ilMailTemplateTypeForm.php';
		//$typeformclass = new ilMailTemplateTypeForm($this->lng, $this->ilCtrl);
		require_once 'Services/MailTemplates/classes/class.gevMailTemplateTypeForm.php';
		$typeformclass = new gevMailTemplateTypeForm($this->lng, $this->ilCtrl);
		// gev-patch end
		$typeformclass->getPopulatedForm($settings_entity);
		$this->tpl->setContent($typeformclass->getHTML());
	}

	/**
	 * @param int $a_template_id
	 * @param string $a_language
	 */
	protected function showTemplateVariantByLanguage($a_template_id, $a_language)
	{
		require_once 'Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php';
		$variant_entity = new ilMailTemplateVariantEntity();
		$variant_entity->setIlDB($this->ilDB);
		$variant_entity->setMailTypesFi($a_template_id);
		$variant_entity->setLanguage($a_language);

		if ($variant_entity->existsByTypeAndLanguage())
		{
			$variant_entity->loadByTypeAndLanguage($a_template_id, $a_language);
		}
		else
		{
			$variant_entity->getEmptyVariant();
		}

		require_once 'Services/MailTemplates/classes/class.ilMailTemplateVariantForm.php';
		$variant_form = new ilMailTemplateVariantForm($this->lng, $this->ilCtrl, $this->ilDB);
		$variant_form->getPopulatedForm($variant_entity);
		$this->tpl->addJavaScript('Services/Mail/js/ilMailComposeFunctions.js');
		$this->tpl->setContent($variant_form->getHTML());
	}

	/**
	 * 
	 */
	protected function showDeleteTemplateTypeConfirmation()
	{
		if(!isset($_POST['template_id']) || !is_array($_POST['template_id']) || !$_POST['template_id'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showTemplateTypesTable();
			return;
		}
		
		require_once './Services/MailTemplates/classes/class.ilSimpleConfirmationGUI.php';
		$confirmation = new ilSimpleConfirmationGUI();

		$ids = array();
		foreach($_POST['template_id'] as $template_id)
		{
			if((int)$template_id)
			{
				$ids[] = $template_id;
			}
		}

		$this->ilCtrl->setParameterByClass('ilMailTemplatesGUI', 'template_ids', implode(',', $ids));
		$confirmation->setFormAction($this->ilCtrl->getFormActionByClass('ilMailTemplatesGUI', 'cancel_delete_template_type'));
		$confirmation->setCancel($this->lng->txt('cancel_delete_template_type'), 'view_template_types');
		$confirmation->setConfirm($this->lng->txt('confirm'), 'delete_template_type');
		$confirmation->setHeaderText($this->lng->txt('confirm_delete_template_type_'.(count($ids) == 1 ? 's' : 'p')));
		$this->tpl->setContent($confirmation->getHTML());
	}

	/**
	 * 
	 */
	protected function handleEditTemplateVariant()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		// Save
		if(
			$_POST['cmd']['save_template_variant'] == $this->lng->txt('save_template_variant') ||
			$_POST['cmd']['save_and_sample_variant'] == $this->lng->txt('save_and_sample_variant')
		)
		{
			require_once 'Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php';
			$entity = new ilMailTemplateVariantEntity();
			$entity->setIlDB($this->ilDB);
			$entity->setId((int)$_POST['mail_template_variant_id']);
			$entity->setMailTypesFi((int)$_POST['mail_types_fi']);
			$entity->setLanguage(ilUtil::stripSlashes($_POST['mail_types_language']));
			$entity->setMessageSubject(ilUtil::stripSlashes($_POST['mail_message_subject']));
			$entity->setMessagePlain($_POST['mail_message_plain']);
			$entity->setMessageHtml($_POST['mail_message_html']);
			$entity->setCreatedDate(ilUtil::stripSlashes($_POST['mail_message_created']));
			$entity->setUpdatedDate(time());
			$entity->setUpdatedUsrFi((int)$ilUser->getId());
			$entity->setTemplateActive((int)$_POST['mail_message_active']);
			$entity->save();		
			
			if ($_POST['cmd']['save_and_sample_variant'] == $this->lng->txt('save_and_sample_variant'))
			{
				require_once './Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
				$settings = new ilMailTemplateSettingsEntity();
				$settings->setIlDB($this->ilDB);
				$settings->loadById($entity->getMailTypesFi());
				$adapter = $settings->getAdapterClassInstance();
				
				/*
				require_once './Services/VoFue/Patch/classes/class.vfMailData.php';
				$mail_data = new vfMailData();				
				$mail_data->setRecipient($ilUser->getId(), $ilUser->getEmail(), $ilUser->getLastName());
				*/
				
				require_once './Services/MailTemplates/classes/class.ilExampleMailData.php';
				$mail_data = new ilExampleMailData();
				$mail_data->setRecipientMailAddress($ilUser->getEmail());
				$mail_data->setBlindCarbonCopyRecipients(array());
				$mail_data->setCarbonCopyRecipients(array());
				$mail_data->setPlaceholders($adapter->getPlaceholderPreviews());
				$mail_data->setRecipientUserId($ilUser->getId());
				if ($adapter->hasAttachmentsPreview())
				{
					foreach ($adapter->getAttachmentsPreview() as $attachment)
					{
						$mail_data->addAttachment($attachment);
					}
				}
				require_once './Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php';
				$api = new ilMailTemplateManagementAPI();
				$api->sendMail(
					$settings->getTemplateCategoryName(), 
					$settings->getTemplateTemplateType(), 
					$entity->getLanguage(),
					$mail_data
				);
			}
			ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
		}

		$this->ilCtrl->redirectByClass('ilMailTemplatesGUI','view_template_types');

	}

	/**
	 * 
	 */
	protected function handleEditTemplateTypeSettings()
	{
		// gev-patch start
		//require_once 'Services/MailTemplates/classes/class.ilMailTemplateTypeForm.php';
		//$typeformclass = new ilMailTemplateTypeForm($this->lng, $this->ilCtrl);
		require_once 'Services/MailTemplates/classes/class.gevMailTemplateTypeForm.php';
		$typeformclass = new gevMailTemplateTypeForm($this->lng, $this->ilCtrl);
		// gev-patch end
		if(!$typeformclass->getForm()->checkInput())
		{
			$typeformclass->getForm()->setValuesByPost();
			$this->tpl->setContent($typeformclass->getHTML());
			return;
		}
		
		// gev-patch start
		$cat_name = $typeformclass->getCategoryName();
		$consumer_location = $typeformclass->getConsumerLocation();
		$template_type = $typeformclass->getTemplateType();
		// gev-patch end
		
		// save settings
		require_once 'Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		$entity = new ilMailTemplateSettingsEntity();
		$entity->setIlDB($this->ilDB);
		// gev-patch start
		$entity->setTemplateCategoryName($cat_name);
		$entity->setTemplateConsumerLocation($consumer_location);
		$entity->setTemplateTemplateType($template_type);
		// gev-patch end
		$entity->setTemplateTypeId((int)$_GET['template_id']);
		$entity->save();
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
		$this->ilCtrl->redirectByClass('ilMailTemplatesGUI','view_template_types');
	}

	/**
	 * 
	 */
	protected function handleNewTemplateTypeSettings()
	{
		// gev-patch start
		//require_once 'Services/MailTemplates/classes/class.ilMailTemplateTypeForm.php';
		//$typeformclass = new ilMailTemplateTypeForm($this->lng, $this->ilCtrl);
		require_once 'Services/MailTemplates/classes/class.gevMailTemplateTypeForm.php';
		$typeformclass = new gevMailTemplateTypeForm($this->lng, $this->ilCtrl);
		// gev-patch end
		$typeformclass->getForm();
		$this->tpl->setContent($typeformclass->getHTML());
	}

	/**
	 * 
	 */
	protected function handleDeleteTemplateType()
	{
		if(!isset($_GET['template_ids']) || !is_array(($template_ids = explode(',', $_GET['template_ids']))))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->showTemplateTypesTable();
			return;
		}

		require_once 'Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php';
		foreach($template_ids as $template_id)
		{
			$settings_entity = new ilMailTemplateSettingsEntity();
			$settings_entity->setIlDB($this->ilDB);
			$settings_entity->loadById($template_id);
			$settings_entity->deleteEntity();
		}
		
		ilUtil::sendSuccess($this->lng->txt('template_type_deleted_'.(count($template_ids) == 1 ? 's' : 'p')), true);
		$this->ilCtrl->redirectByClass('ilMailTemplatesGUI','view_template_types');
	}

	/**
	 *
	 */
	protected function applyTemplateTypeFilter()
	{
		require_once 'Services/MailTemplates/classes/class.ilMailTemplatesTableGUI.php';
		$table = new ilMailTemplatesTableGUI($this, 'view_template_types');
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showTemplateTypeSettings();
	}

	/**
	 *
	 */
	protected function resetTemplateTypeFilter()
	{
		require_once 'Services/MailTemplates/classes/class.ilMailTemplatesTableGUI.php';
		$table = new ilMailTemplatesTableGUI($this, 'view_template_types');
		$table->resetOffset();
		$table->resetFilter();

		$this->showTemplateTypeSettings();
	}
}
