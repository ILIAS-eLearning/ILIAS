<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceDataGatewayFactory
{
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @param ilDB $db
	 */
	public function setDatabaseAdapter(ilDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @return ilDB
	 */
	public function getDatabaseAdapter()
	{
		return $this->db;
	}

	/**
	 * @param string $name
	 * @return ilTermsOfServiceAcceptanceDatabaseGateway
	 * @throws InvalidArgumentException
	 * @throws ilTermsOfServiceMissingDatabaseAdapterException
	 */
	public function getByName($name)
	{
		if(null == $this->db)
		{
			require_once 'Services/TermsOfService/exceptions/class.ilTermsOfServiceMissingDatabaseAdapterException.php';
			throw new ilTermsOfServiceMissingDatabaseAdapterException('Incomplete factory configuration. Please inject a database adapter.');
		}

		switch(strtolower($name))
		{
			case 'iltermsofserviceacceptancedatabasegateway':
				require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceDatabaseGateway.php';
				return new ilTermsOfServiceAcceptanceDatabaseGateway($this->db);

			default:
				throw new InvalidArgumentException('Data gateway not supported');
		}
	}
}
