<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php';

/**
 * Class ilAptarInterfaceLogOverviewPlugin
 */
class ilAptarInterfaceLogOverviewPlugin extends ilUserInterfaceHookPlugin
{
	/**
	 * @var string
	 */
	const CTYPE   = 'Services';

	/**
	 * @var string
	 */
	const CNAME   = 'UIComponent';

	/**
	 * @var string
	 */
	const SLOT_ID = 'uihk';

	/**
	 * @var string
	 */
	const PNAME   = 'AptarInterfaceLogOverview';

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var string
	 */
	protected $save_path;

	/**
	 * @var string
	 */
	protected $from = '';

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilLanguage
	 */
	protected $default_lng;

	/**
	 * {@inheritdoc}
	 */
	protected function init()
	{
		global $ilSetting;

		require_once 'Services/Mail/classes/class.ilMimeMail.php';
		require_once 'Services/Mail/classes/class.ilMail.php';

		$this->save_path = ilUtil::getDataDir() . '/' .self::PNAME . '/';
		$this->from      = ilMail::getIliasMailerAddress();
		
		$this->settings = $ilSetting;

		require_once 'Services/Language/classes/class.ilLanguage.php';
		$this->default_lng = new ilLanguage('en');
	}

	/**
	 * @return string
	 */
	public function getPluginName()
	{
		return self::PNAME;
	}

	/**
	 * @return self|ilPlugin|ilUserInterfaceHookPlugin
	 */
	public static function getInstance()
	{
		if(null !== self::$instance)
		{
			return self::$instance;
		}
		
		return (self::$instance = ilPluginAdmin::getPluginObject(
			self::CTYPE,
			self::CNAME,
			self::SLOT_ID,
			self::PNAME
		));
	}

	/**
	 *
	 */
	public function run()
	{
		require_once dirname(__FILE__) . '/class.ilAptarInterfaceLogOverviewCronTask.php';
		$task = new ilAptarInterfaceLogOverviewCronTask();
		$task->run();
	}

	/**
	 * @param string $file
	 * @param int $errors
	 * @param int $warnings
	 * @param int $data_set_num
	 * @param int $duration
	 * @param int $lowest_error_class_found
	 */
	public function getReportingData($file, $errors, $warnings, $data_set_num, $duration, $lowest_error_class_found)
	{
		/**
		 * @var $ilDB   ilDB
		 * @var $ilSetting ilSetting
		 */
		global $ilDB, $ilSetting;

		if(!is_dir($this->save_path))
		{
			ilUtil::makeDir($this->save_path);
		}
		if(file_exists($file))
		{
			$file_path = $this->save_path . basename($file);
			copy($file, $file_path);
			if(file_exists($file_path))
			{
				$file_size = filesize($file_path);
				$log_id = $ilDB->nextId('aptar_ilo_log_table');
				$ilDB->insert('aptar_ilo_log_table',
					array(
						'log_id'	=> array('integer',	$log_id),
						'file_path'	=> array('text', 	$file_path),
						'errors'	=> array('integer',	$errors),
						'warnings'	=> array('integer',	$warnings),
						'data_sets'	=> array('integer',	$data_set_num),
						'duration'	=> array('integer',	$duration),
						'file_size'	=> array('integer',	$file_size)
					));
				$settings = new ilSetting('customizing_aptar_ilo');
				if($settings->get('is_mail_enabled') == 1)
				{
					$error_level = $settings->get('error_level');
					if($lowest_error_class_found <= $error_level )
					{
						$recipients	= $this->getEmailsForLoginArray(unserialize($settings->get('recipients')));

						$subject 	= sprintf($settings->get('subject'), ilUtil::now());
						$attachment	= null;
						if($settings->get('add_attachment') == 1)
						{
							$dir = ilUtil::getDataDir().'/temp/' . time();
							if(!is_dir($dir))
							{
								ilUtil::makeDir($dir);
							}
							copy($file_path, $dir . '/' . basename($file_path));

							$zip = new ZipArchive();
							$zip->open($dir . '_Logfile.zip', ZipArchive::CREATE);
							$zip->addFile($dir . '/' . basename($file_path), basename($file_path));
							$zip->close();
							$zip = $dir . '_Logfile.zip';

							if(file_exists($zip))
							{
								$attachment = array($zip);
							}
						}
						
						global $lng;
						
						if(!is_object($lng))
						{
							$lng = $this->default_lng;
						}

						$message = sprintf($this->txt('mail_message'),$errors, $warnings, $data_set_num, $duration, $file_size );
						$mail = new ilMimeMail();
						$mail->From($this->from);
						$mail->To($recipients);
						$mail->Subject($subject);
						$mail->Body($message);
						if(is_array($attachment))
						{
							foreach($attachment as $a)
							{
								$mail->Attach($a);
							}
						}
						
						if(!($ilSetting instanceof ilSetting))
						{
							$ilSetting = $this->settings;
						}
						$mail->Send();
					}
				}
			}
			else
			{
				$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewPlugin log file could not be copied.');
			}
		}
		else
		{
			$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewPlugin log file not found.');
		}
	}


	protected function getEmailsForLoginArray($logins)
	{
		$emails = '';
		if(is_array($logins) && sizeof($logins) > 0)
		{
			foreach($logins as $value)
			{
				$user_id = ilObjUser::_loginExists($value);
				if($user_id != false)
				{
					if(strlen($emails))
					{
						$emails .= ',';
					}
					$emails .= ilObjUser::_lookupEmail($user_id);
				}
				else
				{
					$GLOBALS['ilLog']->write('ilAptarInterfaceLogOverviewPlugin user id not found for user : ' . $value .'.');
				}
			}
		}
		return $emails;
	}
}