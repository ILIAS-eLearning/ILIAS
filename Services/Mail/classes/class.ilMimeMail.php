<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';

/**
 * this class encapsulates the PHP mail() function.
 * implements CC, Bcc, Priority headers
 *  include "libmail.php";
 *  $m= new Mail; // create the mail
 *  $m->From( "leo@isp.com" );
 *  $m->To( "destination@somewhere.fr" );
 *  $m->Subject( "the subject of the mail" );
 *  $message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
 *  $m->Body( $message);	// set the body
 *  $m->Cc( "someone@somewhere.fr");
 *  $m->Bcc( "someoneelse@somewhere.fr");
 *  $m->Priority(4) ;	// set the priority to Low
 *  m->Attach( "/home/leo/toto.gif", "image/gif" ) ;	// attach a file of type image/gif
 *  $m->Send();	// send the mail
 *  echo "the mail below has been sent:<br><pre>", $m->Get(), "</pre>";
 *
 * @author	Leo West - lwest@free.fr
 * @version $Id: class.ilMimeMail.php 46278 2013-11-19 13:27:05Z jluetzen $
 *
 *
 */
class ilMimeMail
{
	/**
	 * list of To addresses
	 * @var	array
	 */
	var $sendto = array();

	/**
	 * @var	array
	 */
	var $acc = array();

	/**
	 * @var	array
	 */
	var $abcc = array();

	/**
	 * 	paths of attached files
	 * 	@var array
	 */
	protected $aattach = array();

	/**
	 * @var array
	 */
	protected $adisplay = array();

	/**
	 * 	list of message headers
	 * 	@var array
	 */
	var $xheaders = array();

	/**
	 * 	message priorities referential
	 * 	@var array
	 */
	var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );

	/**
	 * 	character set of message
	 * 	@var string
	 */
	var $charset = "utf-8";
	var $ctencoding = "8bit";
	var $receipt = 0;

	/**
	 * Mail contructor
	 */
	public function __construct()
	{
		$this->autoCheck( false);
		$this->boundary= "--" . md5( uniqid("myboundary") );
	}

	/**
	 * activate or desactivate the email addresses validator
	 * ex: autoCheck( true ) turn the validator on
	 * by default autoCheck feature is on
	 * @param boolean	set to true to turn on the auto validation
	 * @access public
	 */
	function autoCheck($bool )
	{
		if( $bool )
		{
			$this->checkAddress = true;
		}
		else
		{
			$this->checkAddress = false;
		}
	}

	/**
	 * Define the subject line of the email
	 * @param string subject any monoline string
	 */
	function Subject($subject, $a_add_prefix = false)
	{
		if($a_add_prefix)
		{
			// #9096
			include_once "Services/Mail/classes/class.ilMail.php";
			$prefix = ilMail::getSubjectPrefix();
			if(trim($prefix))
			{
				$subject = trim($prefix)." ".$subject;
			}
		}
		$this->xheaders['Subject'] = $subject;
	}

	/**
	 * set the sender of the mail
	 * @param string from should be an email address
	 */
	function From($from )
	{
		if( ! is_string($from) && !is_array($from)) {
			echo "Class Mail: error, From is not a string or array";
			exit;
		}
		if(is_array($from))
		{
			$this->xheaders['From']     = $from[0];
			$this->xheaders['FromName'] = $from[1];
			return;
		}

		$this->xheaders['From'] = $from;
	}

	/**
	 *  set the Reply-to header
	 *  @param string address should be an email address
	 */
	function ReplyTo( $address )
	{
		if( ! is_string($address) )
		{
			return false;
		}

		$this->xheaders["Reply-To"] = $address;
	}

	/**
	 * add a receipt to the mail ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined)
	 * when the receiver opens the message.
	 * @warning this functionality is *not* a standard, thus only some mail clients are compliants.
	 */
	function Receipt()
	{
		$this->receipt = 1;
	}

	/**
	 * set the mail recipient
	 * @param string to email address, accept both a single address or an array of addresses
	 */
	function To( $to )
	{
		// TODO : test validit� sur to
		if( is_array( $to ) )
		{
			$this->sendto= $to;
		}
		else
		{
			$this->sendto[] = $to;
		}

		if( $this->checkAddress == true )
		{
			$this->CheckAdresses( $this->sendto );
		}
	}

	/**
	 *  Cc()
	 *	cc : email address(es), accept both array and string
	 *	@param string cc set the CC headers ( carbon copy )
	 */

	function Cc($cc)
	{
		if( is_array($cc) )
		{
			$this->acc= $cc;
		}
		else
		{
			$this->acc[]= $cc;
		}

		if( $this->checkAddress == true )
		{
			$this->CheckAdresses( $this->acc );
		}
	}

	/**
	 * Bcc()
	 * set the Bcc headers ( blank carbon copy ).
	 * bcc : email address(es), accept both array and string
	 * @param string bcc
	 */
	function Bcc( $bcc )
	{
		if( is_array($bcc) ) 
		{
			$this->abcc = $bcc;
		} 
		else 
		{
			$this->abcc[]= $bcc;
		}

		if( $this->checkAddress == true )
		{
			$this->CheckAdresses( $this->abcc );
		}
	}

	/**
	 * Body( text [, charset] )
	 * set the body (message) of the mail
	 * define the charset if the message contains extended characters (accents)
	 * default to us-ascii
	 * $mail->Body( "m�l en fran�ais avec des accents", "iso-8859-1" );
	 * @param string body
	 * @param string charset (optional)
	 */
	function Body( $body, $charset="" )
	{
		$this->body = $body;

		if( $charset != "" ) 
		{
			$this->charset = strtolower($charset);
			if( $this->charset == "us-ascii" )
			{
				$this->ctencoding = "7bit";
			}
		}
	}

	/**
	 * Organization( $org )
	 * set the Organization header
	 * @param string organization
	 */
	function Organization( $org )
	{
		if( trim( $org != "" )  )
		{
			$this->xheaders['Organization'] = $org;
		}
	}

	/**
	 * Priority( $priority )
	 * set the mail priority
	 * $priority : integer taken between 1 (highest) and 5 ( lowest )
	 * ex: $mail->Priority(1) ; => Highest
	 * @param integer priority
	 */
	function Priority( $priority )
	{
		if( ! intval( $priority ) )
		{
			return false;
		}

		if( ! isset( $this->priorities[$priority-1]) )
		{
			return false;
		}

		$this->xheaders["X-Priority"] = $this->priorities[$priority-1];

		return true;
	}

	/**
	 *  Attach a file to the mail
	 *  @param string filename : path of the file to attach
	 *  @param string filetype : MIME-type of the file. default to 'application/x-unknown-content-type'
	 *  @param string disposition : instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
	 *  @param string $display_name: filename to use in email (if different from source file)
	 */
	function Attach( $filename, $filetype = "", $disposition = "inline", $display_name = null)
	{
		// TODO : si filetype="", alors chercher dans un tablo de MT connus / extension du fichier
		if( $filetype == "" )
		{
			$filetype = "application/octet-stream";
		}

		$this->aattach[] = $filename;
		$this->actype[] = $filetype;
		$this->adispo[] = $disposition;
		$this->adisplay[] = $display_name;
	}

	/**
	 * Build the email message
	 * @access public
	 */
	function BuildMail()
	{
		/**
		 * @var $ilUser          ilObjUser
		 * @var $ilSetting       ilSetting
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilUser, $ilSetting, $ilClientIniFile;

		require_once './Services/Mail/phpmailer/class.phpmailer.php';
		$mail = new PHPMailer();

		$mail->SetFrom($this->xheaders['From'], $this->xheaders['FromName']);
		foreach($this->sendto as $recipients)
		{
			$recipient_pieces = array_filter(array_map('trim', explode(',', $recipients)));
			foreach($recipient_pieces as $recipient)
			{
				$mail->AddAddress($recipient, '');
			}
		}

		foreach($this->acc as $carbon_copies)
		{
			$cc_pieces = array_filter(array_map('trim', explode(',', $carbon_copies)));
			foreach($cc_pieces as $carbon_copy)
			{
				$mail->AddCC($carbon_copy, '');
			}
		}

		foreach($this->abcc as $blind_carbon_copies)
		{
			$bcc_pieces = array_filter(array_map('trim', explode(',', $blind_carbon_copies)));
			foreach($bcc_pieces as $blind_carbon_copy)
			{
				$mail->AddBCC($blind_carbon_copy, '');
			}
		}

		$mail->CharSet = 'utf-8';
		$mail->Subject = $this->xheaders['Subject'];

		if($ilSetting->get('mail_send_html', 0))
		{
			$mail->IsHTML(true);

			$style = $ilClientIniFile->readVariable('layout', 'style');

			$bracket_path = './Services/Mail/templates/default/tpl.html_mail_template.html';
			if($style != 'delos')
			{
				$tplpath = './Customizing/global/skin/' . $style . '/Services/Mail/tpl.html_mail_template.html';

				if(@file_exists($tplpath))
				{
					$bracket_path = './Customizing/global/skin/' . $style . '/Services/Mail/tpl.html_mail_template.html';
				}
			}
			$bracket = file_get_contents($bracket_path);

			if(!$this->body)
			{
				$this->body  = ' ';
			}

			$mail->AltBody = $this->body;
			$mail->Body    = str_replace( '{PLACEHOLDER}', nl2br( ilUtil::makeClickable( $this->body ) ), $bracket );

			$directory = './Services/Mail/templates/default/img/';
			if($style != 'delos')
			{
				$directory = './Customizing/global/skin/' . $style . '/Services/Mail/img/';
			}
			$directory_handle  = @opendir($directory);
			$files = array();
			if($directory_handle)
			{
				while ($filename = @readdir($directory_handle))
				{
					$files[] = $filename;
				}

				$images = preg_grep ('/\.jpg$/i', $files);

				foreach($images as $image)
				{
					$mail->AddEmbeddedImage($directory.$image, 'img/'.$image, $image);
				}
			}
		}
		else
		{
			$mail->IsHTML(false);
			$mail->Body = $this->body;
		}

		$i = 0;
		foreach($this->aattach as $attachment)
		{
			$name = '';
			if(isset($this->adisplay[$i]) && strlen($this->adisplay[$i]) > 0)
			{
				$name = $this->adisplay[$i];
			}

			$mail->AddAttachment($attachment, $name);
			++$i;
		}

		ilLoggerFactory::getLogger('mail')->debug(sprintf(
			"Trying to delegate external email delivery:" .
			" Initiated by: " . $ilUser->getLogin() . " (" . $ilUser->getId() . ")" .
			" | From: " . $this->xheaders['From'] .
			" | To: " . implode(', ', $this->sendto) .
			" | CC: " . implode(', ', $this->abcc) .
			" | BCC: " . implode(', ', $this->acc) .
			" | Subject: " .$mail->Subject
		));

		$result = $mail->Send();

		if($result)
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				'Successfully delegated external mail delivery'
			));
		}
		else
		{
			ilLoggerFactory::getLogger('mail')->debug(sprintf(
				'Could not deliver external email: %s', $mail->ErrorInfo
			));
		}
	}

	/**
	 * 	fornat and send the mail
	 * 	@access public
	 */
	function Send()
	{
		$this->BuildMail();
	}

	/**
	 *		return the whole e-mail , headers + message
	 *		can be used for displaying the message in plain text or logging it
	 */
	function Get()
	{
		$this->BuildMail();
		$mail = "To: " . $this->strTo . "\n";
		$mail .= $this->headers . "\n";
		$mail .= $this->fullBody;
		return $mail;
	}

	/**
	 * 	check an email address validity
	 * 	@access public
	 * 	@param string address : email address to check
	 * 	@return boolean true if email adress is ok
	 */

	function ValidEmail($address)
	{
		if( ereg( ".*<(.+)>", $address, $regs ) ) {
			$address = $regs[1];
		}
		if(ereg( "^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)\$",$address) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * check validity of email addresses
	 * return if unvalid, output an error message and exit, this may -should- be customized
	 * @param	array aad -
	 */
	function CheckAdresses( $aad )
	{
		for($i=0;$i< count( $aad); $i++ ) {
			if( ! $this->ValidEmail( $aad[$i]) ) 
			{
				echo "Class Mail, method Mail : invalid address $aad[$i]";
				exit;
			}
		}
	}

	/**
	 *  check and encode attach file(s) . internal use only
	 *  @access private
	 */
	function _build_attachement()
	{
		$this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"$this->boundary\"";

		$this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
		$this->fullBody .= "Content-Type: text/plain; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n".
			$this->body ."\n";

		$sep= chr(13) . chr(10);

		$ata= array();
		$k=0;

		// for each attached file, do...
		for( $i=0; $i < count( $this->aattach); $i++ ) {

			$filename = $this->aattach[$i];
			$basename = basename($filename);
			$ctype = $this->actype[$i];	// content-type
			$disposition = $this->adispo[$i];
			$display_name = $this->adisplay[$i];
			if(!$display_name)
			{
				$display_name = $basename;
			}

			if( ! file_exists( $filename) ) {
				echo "Class Mail, method attach : file $filename can't be found"; 
				exit;
			}

			$subhdr= "--$this->boundary\nContent-type: $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding:".
				"base64\nContent-Disposition: $disposition;\n  filename=\"$display_name\"\n\n";
			$ata[$k++] = $subhdr;
			// non encoded line length
			$linesz= filesize( $filename)+1;
			$fp= fopen( $filename, 'r' );
			$ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
			fclose($fp);
		}

		$this->fullBody .= implode($sep, $ata);
	}

	function _mimeEncode($a_string)
	{
		$encoded = '=?utf-8?b?';
		$encoded .= base64_encode($a_string);
		$encoded .= '?=';

		return $encoded;
	}
}