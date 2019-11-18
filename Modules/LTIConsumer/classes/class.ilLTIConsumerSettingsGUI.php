<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 *
 * @ilCtrl_Calls ilLTIConsumerSettingsGUI: ilLTIConsumeProviderSettingsGUI
 * @ilCtrl_Calls ilLTIConsumerSettingsGUI: ilCertificateGUI
 */
class ilLTIConsumerSettingsGUI
{
	const SUBTAB_ID_OBJECT_SETTINGS = 'subtab_object_settings';
	const SUBTAB_ID_PROVIDER_SETTINGS = 'subtab_provider_settings';
	const SUBTAB_ID_CERTIFICATE = 'subtab_certificate';
	
	const CMD_SHOW_SETTINGS = 'showSettings';
	const CMD_SAVE_SETTINGS = 'saveSettings';
	const CMD_DELIVER_CERTIFICATE = 'deliverCertificate';
	
	/**
	 * @var ilObjLTIConsumer
	 */
	protected $object;
	
	/**
	 * @var ilLTIConsumerAccess
	 */
	protected $access;
	
	/**
	 * ilLTIConsumerAccess constructor.
	 * @param ilObjLTIConsumer $object
	 */
	public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
	{
		$this->object = $object;
		$this->access = $access;
	}
	
	protected function initSubTabs()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		$DIC->language()->loadLanguageModule('lti');
		$DIC->tabs()->addSubTab(self::SUBTAB_ID_OBJECT_SETTINGS,
			$DIC->language()->txt(self::SUBTAB_ID_OBJECT_SETTINGS),
			$DIC->ctrl()->getLinkTarget($this)
		);
		
		if( $this->needsProviderSettingsSubTab() )
		{
			$DIC->tabs()->addSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS,
				$DIC->language()->txt(self::SUBTAB_ID_PROVIDER_SETTINGS),
				$DIC->ctrl()->getLinkTargetByClass(ilLTIConsumeProviderSettingsGUI::class)
			);
		}

        $validator = new ilCertificateActiveValidator();

		if( $validator->validate() )
		{
			$DIC->tabs()->addSubTab(self::SUBTAB_ID_CERTIFICATE,
				$DIC->language()->txt(self::SUBTAB_ID_CERTIFICATE),
				$DIC->ctrl()->getLinkTargetByClass(ilCertificateGUI::class, 'certificateEditor')
			);
		}
	}
	
	protected function needsProviderSettingsSubTab()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( $this->object->getProvider()->isGlobal() )
		{
			return false;
		}
		
		if( $this->object->getProvider()->getCreator() != $DIC->user()->getId() )
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Execute Command
	 */
	public function executeCommand()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->initSubTabs();
		
		$nc = $DIC->ctrl()->getNextClass();
		
		switch( $nc )
		{
			case strtolower(ilCertificateGUI::class):

                $validator = new ilCertificateActiveValidator();

                if( !$validator->validate() )
				{
					throw new ilCmiXapiException('access denied!');
				}
				
				$DIC->tabs()->activateSubTab(self::SUBTAB_ID_CERTIFICATE);

                $guiFactory = new ilCertificateGUIFactory();
                $gui = $guiFactory->create($this->object);

				$DIC->ctrl()->forwardCommand($gui);
				
				break;

			case strtolower(ilLTIConsumeProviderSettingsGUI::class):
				
				$DIC->tabs()->activateSubTab(self::SUBTAB_ID_PROVIDER_SETTINGS);
				
				$gui = new ilLTIConsumeProviderSettingsGUI($this->object, $this->access);
				$DIC->ctrl()->forwardCommand($gui);
				break;
				
			default:
				
				$DIC->tabs()->activateSubTab(self::SUBTAB_ID_OBJECT_SETTINGS);
				
				$command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS).'Cmd';
				$this->{$command}();
		}
	}
	
	protected function showSettingsCmd(ilLTIConsumerSettingsFormGUI $form = null)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		if( $form === null )
		{
			$form = $this->buildForm();
		}
		
		$DIC->ui()->mainTemplate()->setContent($form->getHTML());
	}
	
	protected function saveSettingsCmd()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = $this->buildForm();
		
		if( $form->checkInput() )
		{
			$oldMasteryScore = $this->object->getMasteryScore();
			
			$form->initObject($this->object);
			$this->object->update();
			
			if( $oldMasteryScore != $this->object->getMasteryScore() )
			{
				ilLPStatusWrapper::_refreshStatus($this->object->getId());
			}
			
			ilUtil::sendSuccess($DIC->language()->txt('msg_obj_modified'), true);
			$DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
		}
		
		$this->showSettingsCmd($form);
	}
	
	protected function buildForm()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$form = new ilLTIConsumerSettingsFormGUI(
			$this->object, $DIC->ctrl()->getFormAction($this),
			self::CMD_SAVE_SETTINGS,self::CMD_SHOW_SETTINGS
		);
		
		return $form;
	}
	
	protected function deliverCertificateCmd()
	{
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $validator = new ilCertificateDownloadValidator();

        if ( !$validator->isCertificateDownloadable((int)$DIC->user()->getId(), (int)$this->object->getId()) )
        {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilObjLTIConsumerGUI::class, ilObjLTIConsumerGUI::DEFAULT_CMD);
        }

        $repository = new ilUserCertificateRepository();

        $certLogger = $DIC->logger()->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $DIC->language()->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int)$DIC->user()->getId(), (int)$this->object->getId());
	}
}
