<?php

require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
require_once('./Services/FileDelivery/classes/Delivery.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
use ILIAS\FileDelivery\Delivery as F;

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
     * @var \ILIAS\DI\Container $dic
     */
    private $dic;


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
        $this->dic = $GLOBALS["DIC"];
	}


	protected function handleRequest() {
		// Set errorreporting
		ilInitialisation::handleErrorReporting();
        $queries = $this->dic->http()->request()->getQueryParams();

		// Set customizing
		if (isset($queries[ilWebAccessChecker::DISPOSITION])) {
			$this->ilWebAccessChecker->setDisposition($_GET[ilWebAccessChecker::DISPOSITION]);
		}
		if (isset($queries[ilWebAccessChecker::STATUS_CODE])) {
			$this->ilWebAccessChecker->setSendStatusCode($_GET[ilWebAccessChecker::STATUS_CODE]);
		}
		if (isset($queries[ilWebAccessChecker::REVALIDATE])) {
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
		$ilFileDelivery = new F('./Services/WebAccessChecker/templates/images/access_denied.png', $this->ilWebAccessChecker->getPathObject()
		                                                                                                                   ->getFileName());
		$ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
		$ilFileDelivery->deliver();
	}


	protected function deliverDummyVideo() {
		$ilFileDelivery = new F('./Services/WebAccessChecker/templates/images/access_denied.mp4', $this->ilWebAccessChecker->getPathObject()
		                                                                                                                   ->getFileName());
		$ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
		$ilFileDelivery->stream();
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleAccessErrors(ilWACException $e) {

        $response = $this->dic->http()
            ->response()
            ->withStatus(401);

        $this->dic->http()->saveResponse($response);

		if ($this->ilWebAccessChecker->getPathObject()->isImage()) {
			$this->deliverDummyImage();
		}
		if ($this->ilWebAccessChecker->getPathObject()->isVideo()) {
			$this->deliverDummyVideo();
		}
		try {
			$this->ilWebAccessChecker->initILIAS();

            //log the error
            $ilLog = $this->dic["ilLoggerFactory"];
            $ilLog->write($e->getMessage());

		} catch (ilWACException $ilWACException) {
		}

		//set the status code again because ilias creates a new DIC.
        $response = $this->dic->http()
            ->response()
            ->withStatus(401);

		$this->dic->http()->saveResponse($response);
	}


	/**
	 * @param ilWACException $e
	 */
	protected function handleErrors(ilWACException $e) {
        $response = $this->dic->http()->response()
            ->withStatus(500);


        /**
         * @var \Psr\Http\Message\StreamInterface $stream
         */
        $stream = $response->getBody();
        $stream->write($e->getMessage());

        $this->dic->http()->saveResponse($response);
	}


	protected function deliver() {
		if (!$this->ilWebAccessChecker->isChecked()) {
			throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
		}

		$ilFileDelivery = new F($this->ilWebAccessChecker->getPathObject()->getPath());
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
