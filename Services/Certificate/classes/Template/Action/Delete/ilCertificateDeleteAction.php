<?php


interface ilCertificateDeleteAction
{
	/**
	 * @param $templateId
	 * @param $objectId
	 * @return mixed
	 */
	public function delete($templateId, $objectId);
}
