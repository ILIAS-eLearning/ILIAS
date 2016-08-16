<?php
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
require_once('./Services/WebAccessChecker/classes/class.ilHTTP.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');

/**
 * Class ilWebAccessCheckerDelivery
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWebAccessCheckerDelivery {

	/**
	 * @var ilWebAccessChecker
	 */
	protected $ilWebAccessChecker = null;


	/**
	 * @param $raw_path
	 */
	public static function run($raw_path) {
		$obj = new self($raw_path);
		$obj->handleRequest();
	}


	/**
	 * ilWebAccessCheckerDelivery constructor.
	 *
	 * @param string $raw_path
	 */
	public function __construct($raw_path) {
		$this->ilWebAccessChecker = new ilWebAccessChecker(rawurldecode($raw_path));
	}


	protected function handleRequest() {
		// Set errorreporting
		ilInitialisation::handleErrorReporting();

		// Set customizing
		if (isset($_GET[ilWebAccessChecker::DISPOSITION])) {
			$this->ilWebAccessChecker->setDisposition($_GET[ilWebAccessChecker::DISPOSITION]);
		}
		if (isset($_GET[ilWebAccessChecker::STATUS_CODE])) {
			$this->ilWebAccessChecker->setSendStatusCode($_GET[ilWebAccessChecker::STATUS_CODE]);
		}
		if (isset($_GET[ilWebAccessChecker::REVALIDATE])) {
			$this->ilWebAccessChecker->setRevalidateFolderTokens($_GET[ilWebAccessChecker::REVALIDATE]);
		}

		// Check if File can be delivered
		try {
			if ($this->ilWebAccessChecker->check()) {
				$this->deliver();
			} else {
				$this->deny();
			}
		} catch (ilWACException $e) {
			switch ($e->getCode()) {
				case ilWACException::ACCESS_DENIED:
				case ilWACException::ACCESS_DENIED_NO_PUB:
				case ilWACException::ACCESS_DENIED_NO_LOGIN:
					$this->handleAccessErrors($e);
					break;
				case ilWACException::ACCESS_WITHOUT_CHECK:
				case ilWACException::INITIALISATION_FAILED:
				case ilWACException::NO_CHECKING_INSTANCE:
				default:
					$this->handleErrors($e);
					break;
			}
		}
	}


	protected function deny() {
		if (!$this->ilWebAccessChecker->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}
		throw new ilWACException(ilWACException::ACCESS_DENIED);
	}


	protected function deliverDummyImage() {
		$ilFileDelivery = new ilFileDelivery('./Services/WebAccessChecker/templates/images/access_denied.png', $this->ilWebAccessChecker->getPathObject()
		                                                                                                                                ->getFileName());
		$ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
		$ilFileDelivery->deliver();
	}


	protected function deliverDummyVideo() {
		$ilFileDelivery = new ilFileDelivery('./Services/WebAccessChecker/templates/images/access_denied.mp4', $this->ilWebAccessChecker->getPathObject()
		                                                                                                                                ->getFileName());
		$ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
		$ilFileDelivery->stream();
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleAccessErrors(ilWACException $e) {
		if ($this->ilWebAccessChecker->isSendStatusCode()) {
			ilHTTP::status(401);
		}
		if ($this->ilWebAccessChecker->getPathObject()->isImage()) {
			$this->deliverDummyImage();
		}
		if ($this->ilWebAccessChecker->getPathObject()->isVideo()) {
			$this->deliverDummyVideo();
		}
		try {
			$this->ilWebAccessChecker->initILIAS();
		} catch (ilWACException $ilWACException) {
		}

		global $tpl, $ilLog;
		$ilLog->write($e->getMessage());
		$tpl->setVariable('BASE', strstr($_SERVER['REQUEST_URI'], '/data', true) . '/');
		ilUtil::sendFailure($e->getMessage());
		$tpl->getStandardTemplate();
		$tpl->show();
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleErrors(ilWACException $e) {
		ilHTTP::status(500);
		echo $e->getMessage();
	}


	protected function deliver() {
		if (!$this->ilWebAccessChecker->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}

		$ilFileDelivery = new ilFileDelivery($this->ilWebAccessChecker->getPathObject()->getPath());
		$ilFileDelivery->setCache(false);
		$ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
		ilWACLog::getInstance()->write('Deliver file using ' . $ilFileDelivery->getDeliveryType());
		if ($this->ilWebAccessChecker->getPathObject()->isStreamable()) { // fixed 0016468
			ilWACLog::getInstance()->write('begin streaming');
			$ilFileDelivery->stream();
		} else {
			$ilFileDelivery->deliver();
		}
	}
}
