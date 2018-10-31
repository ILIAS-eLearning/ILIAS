<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilMailAddressType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilMailAddressType
{
	/**
	 * Returns an array of resolved user ids
	 * @return int[]
	 */
	public function resolve(): array;

	/**
	 * @param $senderId integer
	 * @return bool
	 */
	public function validate(int $senderId): bool;
	
	/** @return array */
	public function getErrors(): array;

	/**
	 * @return \ilMailAddress
	 */
	public function getAddress(): \ilMailAddress;
}