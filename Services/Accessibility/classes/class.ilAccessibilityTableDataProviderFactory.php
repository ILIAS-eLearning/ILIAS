<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityTableDataProviderFactory
 */
class ilAccessibilityTableDataProviderFactory
{
	const CONTEXT_DOCUMENTS = 'documents';

	/** @var ilDBInterface|null */
	protected $db;

	/**
	 * @param string $context
	 * @return ilAccessibilityTableDataProvider
	 * @throws ilAccessibilityMissingDatabaseAdapterException
	 * @throws InvalidArgumentException
	 */
	public function getByContext(string $context) : ilAccessibilityTableDataProvider
	{
		switch ($context) {
			case self::CONTEXT_DOCUMENTS:
				return new ilAccessibilityDocumentTableDataProvider();

			default:
				throw new InvalidArgumentException('Provider not supported');
		}
	}

	/**
	 * @param array $mandatoryMemberVariables
	 * @throws ilAccessibilityMissingDatabaseAdapterException
	 */
	protected function validateConfiguration(array $mandatoryMemberVariables) : void
	{
		foreach ($mandatoryMemberVariables as $member) {
			if (null === $this->{$member}) {
				$exception = $this->getExceptionByMember($member);
				throw $exception;
			}
		}
	}

	/**
	 * @param string $member
	 * @return ilAccessibilityMissingDatabaseAdapterException
	 * @throws InvalidArgumentException
	 */
	protected function getExceptionByMember(string $member)
	{
		switch ($member) {
			case 'db':
				return new ilAccessibilityMissingDatabaseAdapterException(
					'Incomplete factory configuration. Please inject a database adapter.'
				);

			default:
				throw new InvalidArgumentException("Exception for member {$member} not supported");
		}
	}

	/**
	 * @param ilDBInterface|null $db
	 */
	public function setDatabaseAdapter(?ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @return ilDBInterface|null
	 */
	public function getDatabaseAdapter() : ?ilDBInterface
	{
		return $this->db;
	}
}
