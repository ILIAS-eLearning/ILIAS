<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityCriterionConfig
 */
class ilAccessibilityCriterionConfig extends ArrayObject implements ilAccessibilityJsonSerializable
{
	/**
	 * ilAccessibilityCriterionConfig constructor.
	 * @param string|array
	 */
	public function __construct($data = [])
	{
		if (is_array($data)) {
			parent::__construct($data);
		} else {
			parent::__construct([]);

			if (is_string($data)) {
				$this->fromJson($data);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function toJson() : string
	{
		$json = json_encode($this);

		return $json;
	}

	/**
	 * @inheritdoc
	 */
	public function fromJson(string $json) : void
	{
		$data = json_decode($json, true);

		$this->exchangeArray($data);
	}

	/**
	 * @inheritdoc
	 */
	public function jsonSerialize()
	{
		return $this->getArrayCopy();
	}
}