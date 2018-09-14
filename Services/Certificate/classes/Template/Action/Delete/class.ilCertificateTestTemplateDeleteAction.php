<?php


class ilCertificateTestTemplateDeleteAction implements ilCertificateDeleteAction
{
	const CERTIFICATE_VISIBILITY_DEFAULT_VALUE = 0;

	/**
	 * @var ilCertificateDeleteAction
	 */
	private $deleteAction;

	/**
	 * @var ilCertificateObjectHelper
	 */
	private $objectHelper;

	public function __construct(
		ilCertificateDeleteAction $deleteAction,
		ilCertificateObjectHelper $objectHelper
	) {
		$this->deleteAction = $deleteAction;
		$this->objectHelper = $objectHelper;
	}

	/**
	 * @param $templateId
	 * @param $objectId
	 * @return mixed
	 */
	public function delete($templateId, $objectId)
	{
		$this->deleteAction->delete($templateId, $objectId);

		/** @var ilObjTest $object */
		$object = $this->objectHelper->getInstanceByObjId($objectId);
		$object->saveCertificateVisibility(self::CERTIFICATE_VISIBILITY_DEFAULT_VALUE);
	}
}
