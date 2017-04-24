<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMimeMail
 */
class ilMimeMail
{
	/**
	 * @var ilMailMimeTransport|null
	 */
	protected static $defaultTransport;

	/**
	 * @var string
	 */
	protected $subject = '';

	/**
	 * @var string
	 */
	protected $body = '';

	/**
	 * @var string
	 */
	protected $final_body = '';

	/**
	 * @var string
	 */
	protected $final_body_alt = '';

	/**
	 * list of To addresses
	 * @var	array
	 */
	protected $sendto = array();

	/**
	 * @var	array
	 */
	protected $acc = array();

	/**
	 * @var	array
	 */
	protected $abcc = array();

	/**
	 * @var array
	 */
	protected $images = array();

	/**
	 * 	paths of attached files
	 * 	@var array
	 */
	protected $aattach = array();

	/**
	 * @var array
	 */
	protected $actype = array();

	/**
	 * @var array
	 */
	protected $adispo = array();

	/**
	 * @var array
	 */
	protected $adisplay = array();

	/**
	 * @var ilMailMimeSender
	 */
	protected $sender; 

	/**
	 * ilMimeMail constructor.
	 */
	public function __construct()
	{
		global $DIC;

		if(!(self::getDefaultTransport() instanceof ilMailMimeTransport))
		{
			$factory = $DIC["mail.mime.transport.factory"];
			self::setDefaultTransport($factory->getTransport());
		}
	}

	/**
	 * @param \ilMailMimeTransport|null $transport
	 * @throws \InvalidArgumentException
	 */
	public static function setDefaultTransport($transport)
	{
		if(!is_null($transport) && !($transport instanceof \ilMailMimeTransport))
		{
			throw new \InvalidArgumentException(\sprintf(
				"The passed argument must be null or of type 'ilMailMimeTransport', %s given!", gettype($transport)
			));
		}

		self::$defaultTransport = $transport;
	}

	/**
	 * @return ilMailMimeTransport|null
	 */
	public static function getDefaultTransport()
	{
		return self::$defaultTransport;
	}

	/**
	 * @param string $subject Define the subject line of the email
	 * @param bool   $a_add_prefix
	 */
	public function Subject($subject, $a_add_prefix = false)
	{
		if($a_add_prefix)
		{
			// #9096
			require_once 'Services/Mail/classes/class.ilMail.php';
			$prefix = ilMail::getSubjectPrefix();
			if(trim($prefix))
			{
				$subject = trim($prefix) . ' ' . $subject;
			}
		}

		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param ilMailMimeSender $sender
	 */
	public function From(ilMailMimeSender $sender)
	{
		$this->sender = $sender;
	}

	/**
	 * Set the mail recipient
	 * @param string|array To email address, accept both a single address or an array of addresses
	 */
	public function To($to)
	{
		if(is_array($to))
		{
			$this->sendto = $to;
		}
		else
		{
			$this->sendto[] = $to;
		}
	}

	/**
	 * Set the cc mail recipient
	 * @param string|array CC email address, accept both a single address or an array of addresses
	 */
	public function Cc($cc)
	{
		if(is_array($cc))
		{
			$this->acc = $cc;
		}
		else
		{
			$this->acc[] = $cc;
		}
	}

	/**
	 * Set the bcc mail recipient
	 * @param string|array BCC email address, accept both a single address or an array of addresses
	 */
	public function Bcc($bcc)
	{
		if(is_array($bcc)) 
		{
			$this->abcc = $bcc;
		} 
		else 
		{
			$this->abcc[] = $bcc;
		}
	}

	/**
	 * @return array
	 */
	public function getTo()
	{
		return $this->sendto;
	}

	/**
	 * @return array
	 */
	public function getCc()
	{
		return $this->acc;
	}

	/**
	 * @return array
	 */
	public function getBcc()
	{
		return $this->abcc;
	}

	/**
	 * @param string $body
	 */
	public function Body($body)
	{
		$this->body = $body;

		$this->final_body     = '';
		$this->final_body_alt = '';
	}

	/**
	 * @return string
	 */
	public function getFinalBody()
	{
		return $this->final_body;
	}

	/**
	 * @return string
	 */
	public function getFinalBodyAlt()
	{
		return $this->final_body_alt;
	}

	/**
	 * @return ilMailMimeSender
	 */
	public function getFrom()
	{
		return $this->sender;
	}

	/**
	 * Attach a file to the mail
	 * @param string $filename     Path of the file to attach
	 * @param string $file_type    MIME-type of the file. default to 'application/x-unknown-content-type'
	 * @param string $disposition  Instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
	 * @param string $display_name Filename to use in email (if different from source file)
	 */
	public function Attach($filename, $file_type = '', $disposition = 'inline', $display_name = null)
	{
		if($file_type == '')
		{
			$file_type = 'application/octet-stream';
		}

		$this->aattach[]  = $filename;
		$this->actype[]   = $file_type;
		$this->adispo[]   = $disposition;
		$this->adisplay[] = $display_name;
	}

	/**
	 * @return array An array of attachments. Each element must container to associative keys, 'path' and 'name'
	 */
	public function getAttachments()
	{
		$attachments = array();

		$i = 0;
		foreach($this->aattach as $attachment)
		{
			$name = '';
			if(isset($this->adisplay[$i]) && strlen($this->adisplay[$i]) > 0)
			{
				$name = $this->adisplay[$i];
			}

			$attachments[] = array(
				'path' => $attachment,
				'name' => $name
			);
			++$i;
		}

		return $attachments;
	}

	/**
	 * @return array An array of images. Each element must container to associative keys, 'path', 'cid' and 'name'
	 */
	public function getImages()
	{
		return $this->images;
	}

	/**
	 * Build the relevant email data
	 */
	protected function build()
	{
		/**
		 * @var $ilUser          ilObjUser
		 * @var $ilSetting       ilSetting
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilSetting, $ilClientIniFile;

		$this->images = array();

		if($ilSetting->get('mail_send_html', 0))
		{
			$skin = $ilClientIniFile->readVariable('layout', 'skin');

			$bracket_path = './Services/Mail/templates/default/tpl.html_mail_template.html';
			if($skin != 'delos')
			{
				$tplpath = './Customizing/global/skin/' . $skin . '/Services/Mail/tpl.html_mail_template.html';

				if(@file_exists($tplpath))
				{
					$bracket_path = './Customizing/global/skin/' . $skin . '/Services/Mail/tpl.html_mail_template.html';
				}
			}
			$bracket = file_get_contents($bracket_path);

			if(!$this->body)
			{
				$this->body  = ' ';
			}

			$this->final_body_alt = $this->body;

			if(strip_tags($this->body, '<b><u><i><a>') == $this->body)
			{
				// Let's assume that there is no HTML, so convert "\n" to "<br>" 
				$this->body = nl2br($this->body);
			}
			$this->final_body = str_replace('{PLACEHOLDER}', ilUtil::makeClickable($this->body), $bracket);

			$directory = './Services/Mail/templates/default/img/';
			if($skin != 'delos')
			{
				$directory = './Customizing/global/skin/' . $skin . '/Services/Mail/img/';
			}
			$directory_handle  = @opendir($directory);
			$files = array();
			if($directory_handle)
			{
				while ($filename = @readdir($directory_handle))
				{
					$files[] = $filename;
				}

				$images = preg_grep('/\.jpg$/i', $files);
				foreach($images as $image)
				{
					$this->images[] = array(
						'path' => $directory . $image,
						'cid'  => 'img/' . $image,
						'name' => $image
					);
				}
			}
		}
		else
		{
			$this->final_body = $this->body;
		}
	}

	/**
	 * @param $transport ilMailMimeTransport|null
	 */
	public function Send($transport = null)
	{
		if(!($transport instanceof ilMailMimeTransport))
		{
			$transport = self::getDefaultTransport();
		}

		$this->build();
		$transport->send($this);
	}
}