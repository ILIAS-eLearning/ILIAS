<?php

interface ilCertificatePlaceholderValues
{
	/**
	 * @param $userId
	 * @param $objId
	 * @return mixed
	 */
	public function getPlaceholderValues($userId, $objId);

	/**
	 * @return mixed
	 */
	public function getPlaceholderDescription();
}
