<?php


class ilLearningOutcomeGUI
{
	/**
	 * @var mixed|null
	 */
	private $template;

	/**
	 * @var ilCtrl|null
	 */
	private $controller;

	/**
	 * @var ilObjUser|null
	 */
	private $user;

	/**
	 * @var ilUserCertificateRepository
	 */
	private $repository;

	public function __construct($template = null, $controller = null, $user = null, ilUserCertificateRepository $repository = null)
	{
		global $DIC;

		if ($template === null) {
			$template = $DIC->ui()->mainTemplate();
		}
		$this->template = $template;

		if ($controller === null) {
			$controller = $DIC->ctrl();
		}
		$this->controller = $controller;

		if ($user === null) {
			$user = $DIC->user();
		}
		$this->user = $user;

		if ($repository === null) {
			$repository = new ilUserCertificateRepository();
		}
		$this->repository = $repository;
	}

	/**
	 * execute command
	 */
	public function executeCommand()
	{
		$nextClass = $this->ctrl->getNextClass();

		switch($nextClass)
		{
			case 'showBadgesTable':
				break;
			case 'showCertificateTable':
				break;
			default:
				$cmd = $this->ctrl->getCmd('showCertificateTable');
				$this->$cmd();
				break;
		}
		return true;
	}

	public function showCertificateTable()
	{
		$template = new ilTemplate("tpl.certificate_table.html", true, true, "Services/LearningOutcome");

		$certificates = $this->repository->fetchActiveCertificates($this->user->getId());

		ilDatePresentation::setUseRelativeDates(false);

		foreach($certificates as $certificate)
		{
			if ($certificate['active']) {
				$object = ilObjectFactory::getInstanceByObjId($certificate['obj_id'], false);

				$title = $object->getTitle();

				$acqiuredTimestamp = ilDatePresentation::formatDate(new ilDate($certificate['acquired_timestamp']));

				$template->setCurrentBlock('table_entry');

				$template->setVariable('ID', $certificate['id']);
				$template->setVariable('CERTIFICATE_AUTHORITY', $title);
				$template->setVariable('ACQUIRED_DATE', $acqiuredTimestamp);

				$template->parseCurrentBlock();
			}
		}

		$this->template->setContent($template->get());
	}

	public function showBadgesTable()
	{
		// Suche Badges GUI Klasse in bette sie ins Template ein
	}
}
