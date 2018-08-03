<?php


class ilCertificateTestTemplateDeleteAction implements ilCertificateDeleteAction
{
	/**
	 * @var ilCertificateDeleteAction
	 */
	private $deleteAction;

	const CERTIFICATE_VISIBILITY_DEFAULT_VALUE = 0;

	public function __construct(ilCertificateDeleteAction $deleteAction)
	{
		$this->deleteAction = $deleteAction;
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
		$object = ilObjectFactory::getInstanceByObjId($objectId);
		$object->saveCertificateVisibility(self::CERTIFICATE_VISIBILITY_DEFAULT_VALUE);
	}
}
