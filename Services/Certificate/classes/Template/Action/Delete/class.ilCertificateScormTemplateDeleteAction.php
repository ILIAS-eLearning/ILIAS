<?php


class ilCertificateScormTemplateDeleteAction implements ilCertificateDeleteAction
{
	/**
	 * @var ilCertificateTemplateDeleteAction
	 */
	private $deleteAction;

	/**
	 * @param ilCertificateTemplateDeleteAction $deleteAction
	 */
	public function __construct(ilCertificateTemplateDeleteAction $deleteAction)
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

		$scormSetting = new ilSetting('scorm');
		$scormSetting->delete('certificate_' . $objectId);
	}
}
