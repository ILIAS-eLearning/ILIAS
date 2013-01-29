<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceEntity.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceEntity implements ilTermsOfServiceEntity
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var string
	 */
	protected $signed_text;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var int
	 */
	protected $timestamp;

	/**
	 * @var string
	 */
	protected $path_to_file;

	/**
	 * @var string
	 */
	protected $hash = '';

	/**
	 * @param string $hash
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * @var ilTermsOfServiceAcceptanceDataGateway
	 */
	protected $data_gateway;

	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $signed_text
	 */
	public function setSignedText($signed_text)
	{
		$this->signed_text = $signed_text;
	}

	/**
	 * @return string
	 */
	public function getSignedText()
	{
		return $this->signed_text;
	}

	/**
	 * @param int $timestamp
	 */
	public function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}

	/**
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * @param string $path_to_file
	 */
	public function setPathToFile($path_to_file)
	{
		$this->path_to_file = $path_to_file;
	}

	/**
	 * @return string
	 */
	public function getPathToFile()
	{
		return $this->path_to_file;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @throws ilTermsOfServiceMissingDataGatewayException
	 */
	public function save()
	{
		if(null === $this->data_gateway)
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingDataGatewayException.php';
			throw new ilTermsOfServiceMissingDataGatewayException('Incomplete entity configuration. Please inject a data gateway.');
		}

		$this->data_gateway->save($this);
	}

	/**
	 * @param ilTermsOfServiceAcceptanceDataGateway $data_gateway
	 */
	public function setDataGateway($data_gateway)
	{
		$this->data_gateway = $data_gateway;
	}

	/**
	 * @return ilTermsOfServiceAcceptanceDataGateway
	 */
	public function getDataGateway()
	{
		return $this->data_gateway;
	}

	/**
	 * @throws ilTermsOfServiceEntityNotFoundException
	 */
	public function loadCurrentOfUser()
	{
		$this->data_gateway->loadCurrentOfUser($this);

		if(!$this->getId())
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceEntityNotFoundException.php';
			throw new ilTermsOfServiceEntityNotFoundException('No acceptance found for the passed user.');
		}
	}
}
