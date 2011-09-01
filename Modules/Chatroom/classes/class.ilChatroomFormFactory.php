<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomFormFactory
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomFormFactory
{

	/**
	 * Constructor
	 *
	 * Requires ilPropertyFormGUI
	 */
	public function __construct()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
	}

	/**
	 * Instantiates and returns ilPropertyFormGUI containing ilTextInputGUI
	 * and ilTextAreaInputGUI
	 * 
	 * @deprecated replaced by default creation screens
	 *
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getCreationForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();
		$title = new ilTextInputGUI( $lng->txt( 'title' ), 'title' );
		$title->setRequired( true );
		$form->addItem( $title );

		$description = new ilTextAreaInputGUI( $lng->txt( 'description' ), 'desc' );
		$form->addItem( $description );

		return $this->addDefaultBehaviour( $form );
	}

	/**
	 * Applies given values to field in given form.
	 *
	 * @param ilPropertyFormGUI $form
	 * @param array $values
	 * @todo: $values typehint array?
	 */
	public static function applyValues(ilPropertyFormGUI $form, $values)
	{
		foreach( $values as $key => $value )
		{
			$field = $form->getItemByPostVar( $key );

			if( !$field )
			continue;

			switch(strtolower( get_class( $field ) ))
			{
				case 'ilcheckboxinputgui':
					if( $value )
					{
						$field->setChecked( true );
					}
					break;

				default:
					$field->setValue( $value );
			}
		}
	}

	/**
	 * Returns settings form.
	 *
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getSettingsForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();
		$title = new ilTextInputGUI( $lng->txt( 'title' ), 'title' );
		$title->setRequired( true );
		$form->addItem( $title );

		$description = new ilTextAreaInputGUI( $lng->txt( 'description' ), 'desc' );
		$form->addItem( $description );

		$cb = new ilCheckboxInputGUI( $lng->txt( 'allow_anonymous' ), 'allow_anonymous' );
		$form->addItem( $cb );

		$txt = new ilTextInputGUI( $lng->txt( 'autogen_usernames' ), 'autogen_usernames' );
		$txt->setRequired( true );
		$txt->setInfo( $lng->txt( 'autogen_usernames_info' ) );
		$form->addItem( $txt );

		$cb = new ilCheckboxInputGUI( $lng->txt( 'allow_custom_usernames' ), 'allow_custom_usernames' );
		$form->addItem( $cb );

		$cb_history = new ilCheckboxInputGUI( $lng->txt( 'enable_history' ), 'enable_history' );
		$form->addItem( $cb_history );
		/*
		 $cb = new ilCheckboxInputGUI( $lng->txt( 'restrict_history' ), 'restrict_history' );
		 $cb->setInfo( $lng->txt( 'restrict_history_info' ) );
		 $cb_history->addSubItem( $cb );
		 */
		//$cb = new ilCheckboxInputGUI( $lng->txt( 'allow_private_rooms' ), 'allow_private_rooms' );
		//$form->addItem( $cb );

		return $form;
	}

	/**
	 * Prepares Fileupload form and returns it.
	 *
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getFileUploadForm()
	{
		global $lng;

		$form		= new ilPropertyFormGUI();
		$file_input = new ilFileInputGUI();

		$file_input->setPostVar('file_to_upload');
		$file_input->setTitle( $lng->txt( 'upload' ) );
		$form->addItem( $file_input );
		$form->addCommandButton( 'UploadFile-uploadFile', $lng->txt( 'submit' ) );

		$form->setTarget('_blank');

		return $form;
	}

	/**
	 * Adds 'create-save' and 'cancel' button to given $form and returns it.
	 *
	 * @global ilLanguage $lng
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	private function addDefaultBehaviour(ilPropertyFormGUI $form)
	{
		global $lng;

		$form->addCommandButton( 'create-save', $lng->txt( 'create' ) );
		$form->addCommandButton( 'cancel', $lng->txt( 'cancel' ) );

		return $form;
	}

	/**
	 * Returns period form.
	 *
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getPeriodForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();

		require_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
		$duration = new ilDateDurationInputGUI( $lng->txt( 'period' ), 'timeperiod' );

		$duration->setStartText($lng->txt('duration_from'));
		$duration->setEndText($lng->txt('duration_to'));
		$duration->setShowTime( true );
		$form->addItem( $duration );

		return $form;
	}

	/**
	 * Returns chatname selection form.
	 *
	 * @global ilLanguage $lng
	 * @param array $name_options
	 * @return ilPropertyFormGUI
	 */
	public function getUserChatNameSelectionForm(array $name_options)
	{
		global $lng;

		$form = new ilPropertyFormGUI();
		$radio = new ilRadioGroupInputGUI(
		$lng->txt( 'select_custom_username' ), 'custom_username_radio'
		);

		foreach( $name_options as $key => $option )
		{
			$opt = new ilRadioOption( $option, $key );
			$radio->addOption( $opt );
		}

		$custom_opt = new ilRadioOption(
		$lng->txt( 'custom_username' ), 'custom_username'
		);

		$radio->addOption( $custom_opt );

		$txt = new ilTextInputGUI(
		$lng->txt( 'custom_username' ), 'custom_username_text'
		);

		$custom_opt->addSubItem( $txt );
		$form->addItem( $radio );

		/**
		 * @todo irgendwie anders machen :)
		 */
		$radio->setValue( 'fullname' );

		return $form;
	}

	/**
	 * Returns session form with period set by given $sessions.
	 *
	 * @global ilLanguage $lng
	 * @param array $sessions
	 * @return ilPropertyFormGUI
	 * @todo: $sessions typehint array?
	 */
	public function getSessionForm($sessions)
	{
		global $lng;

		$form = new ilPropertyFormGUI();
		$list = new ilSelectInputGUI( $lng->txt( 'session' ), 'session' );

		$options = array();

		foreach( $sessions as $session )
		{
			$start = new ilDateTime( $session['connected'], IL_CAL_UNIX );
			$end = new ilDateTime( $session['disconnected'], IL_CAL_UNIX );

			$options[$session['connected'] . ',' .
			$session['disconnected']] = ilDatePresentation::formatPeriod( $start, $end );
		}

		$list->setOptions( $options );
		$list->setRequired( true );

		$form->addItem( $list );

		return $form;
	}

	/**
	 * Returns general settings form.
	 *
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getGeneralSettingsForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();

		$address = new ilTextInputGUI( $lng->txt( 'address' ), 'address' );
		$address->setRequired( true );
		$form->addItem( $address );

		$port = new ilNumberInputGUI( $lng->txt( 'port' ), 'port' );
		$port->setMinValue( 1 );
		$port->setMaxValue( 65535 );
		$port->setRequired( true );
		$port->setInfo($lng->txt('port_info'));
		$form->addItem( $port );

		/*
		$instance = new ilTextInputGUI( $lng->txt( 'instance' ), 'instance' );
		$instance->setRequired( true );
		$form->addItem( $instance );
		*/
		$priv_hosts = new ilTextInputGUI( $lng->txt( 'priv_hosts' ), 'priv_hosts' );
		$priv_hosts->setRequired( true );
		$form->addItem( $priv_hosts );

		$keystore = new ilTextInputGUI( $lng->txt( 'keystore' ), 'keystore' );
		$keystore->setRequired( true );
		$keypass = new ilTextInputGUI( $lng->txt( 'keypass' ), 'keypass' );
		$keypass->setRequired( true );
		$storepass = new ilTextInputGUI( $lng->txt( 'storepass' ), 'storepass' );
		$storepass->setRequired( true );

		$protocol = new ilRadioGroupInputGUI( $lng->txt( 'protocol' ), 'protocol' );
		$http = new ilRadioOption( $lng->txt( 'http' ), 'http' );
		$https = new ilRadioOption( $lng->txt( 'https' ), 'https' );
		$https->addSubItem( $keystore );
		$https->addSubItem( $keypass );
		$https->addSubItem( $storepass );
		$protocol->addOption( $http );
		$protocol->addOption( $https );
		$form->addItem( $protocol );

		return $form;
	}

	public function getClientSettingsForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();

		$cb = new ilCheckboxInputGUI( $lng->txt( 'chat_enabled' ), 'chat_enabled' );
		$form->addItem( $cb );

		$cb = new ilCheckboxInputGUI( $lng->txt( 'enable_osd' ), 'enable_osd' );
		$form->addItem( $cb );

		$txt = new ilNumberInputGUI( $lng->txt( 'osd_intervall' ), 'osd_intervall' );
		$txt->setMinValue(1);
		$txt->setRequired( true );
		$cb->addSubItem( $txt );

		/*$hash = new ilTextInputGUI( $lng->txt( 'hash' ), 'hash' );
		$hash->setRequired( true );
		$form->addItem( $hash );*/

		$name = new ilTextInputGUI( $lng->txt( 'name' ), 'name' );
		$name->setRequired( true );
		$form->addItem( $name );

		$url = new ilTextInputGUI( $lng->txt( 'url' ), 'url' );
		$url->setRequired( true );
		$form->addItem( $url );

		$user = new ilTextInputGUI( $lng->txt( 'user' ), 'user' );
		$user->setRequired( true );
		$form->addItem( $user );

		$password = new ilPasswordInputGUI( $lng->txt( 'password' ), 'password' );
		$password->setRequired( true );
		$form->addItem( $password );

		$client = new ilTextInputGUI( $lng->txt( 'client' ), 'client' );
		$client->setRequired( true );
		//$client->setValue( CLIENT_ID );
		$form->addItem( $client );

		return $form;
	}

}

?>