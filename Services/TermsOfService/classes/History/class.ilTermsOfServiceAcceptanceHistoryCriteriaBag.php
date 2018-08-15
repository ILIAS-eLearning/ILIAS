<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBag
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryCriteriaBag extends \ArrayObject implements \ilTermsOfServiceJsonSerializable
{
	/**
	 * ilTermsOfServiceAcceptanceHistoryCriteriaBag constructor.
	 * @param string|array
	 */
	public function __construct($data = [])
	{
		if (is_array($data)) {
			parent::__construct(array_values(array_map(function(\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment) {
				return [
					'id' => $criterionAssignment->getCriterionId(),
					'value' => $criterionAssignment->getCriterionValue()
				];
			}, $data)));
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
	public function toJson(): string
	{
		$json = json_encode($this);

		return $json;
	}

	/**
	 * @inheritdoc
	 */
	public function fromJson(string $json)
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