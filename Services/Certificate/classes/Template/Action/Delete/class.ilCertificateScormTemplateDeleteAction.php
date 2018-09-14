<?php


class ilCertificateScormTemplateDeleteAction implements ilCertificateDeleteAction
{
	/**
	 * @var ilCertificateTemplateDeleteAction
	 */
	private $deleteAction;

	/**
	 * @var ilSetting|null
	 */
	private $setting;

	/**
	 * @param ilCertificateTemplateDeleteAction $deleteAction
	 * @param ilSetting|null $setting
	 */
	public function __construct(ilCertificateTemplateDeleteAction $deleteAction, ilSetting $setting = null)
	{
		$this->deleteAction = $deleteAction;

		if (null === $setting) {
			$setting = new ilSetting('scorm');
		}
		$this->setting = $setting;
	}

	/**
	 * @param $templateId
	 * @param $objectId
	 * @return mixed
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 */
	public function delete($templateId, $objectId)
	{
		$this->deleteAction->delete($templateId, $objectId);

		$this->setting->delete('certificate_' . $objectId);
	}
}
