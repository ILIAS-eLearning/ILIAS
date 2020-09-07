<?php
// declare(strict_types=1);

require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
require_once('./Services/FileDelivery/classes/Delivery.php');
require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');

use ILIAS\FileDelivery\Delivery;
use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\GlobalHttpState;

/**
 * Class ilWebAccessCheckerDelivery
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWebAccessCheckerDelivery
{

    /**
     * @var ilWebAccessChecker
     */
    private $ilWebAccessChecker = null;
    /**
     * @var GlobalHttpState $http
     */
    private $http;


    /**
     * @param GlobalHttpState $httpState
     * @param CookieFactory   $cookieFactory
     *
     * @return void
     */
    public static function run(GlobalHttpState $httpState, CookieFactory $cookieFactory)
    {
        $obj = new self($httpState, $cookieFactory);
        $obj->handleRequest();
    }


    /**
     * ilWebAccessCheckerDelivery constructor.
     *
     * @param GlobalHttpState $httpState
     * @param CookieFactory   $cookieFactory
     */
    public function __construct(GlobalHttpState $httpState, CookieFactory $cookieFactory)
    {
        $this->ilWebAccessChecker = new ilWebAccessChecker($httpState, $cookieFactory);
        $this->http = $httpState;
    }


    /**
     * @return void
     */
    protected function handleRequest()
    {
        // Set errorreporting
        ilInitialisation::handleErrorReporting();
        $queries = $this->http->request()->getQueryParams();

        // Set customizing
        if (isset($queries[ilWebAccessChecker::DISPOSITION])) {
            $this->ilWebAccessChecker->setDisposition($queries[ilWebAccessChecker::DISPOSITION]);
        }
        if (isset($queries[ilWebAccessChecker::STATUS_CODE])) {
            $this->ilWebAccessChecker->setSendStatusCode($queries[ilWebAccessChecker::STATUS_CODE]);
        }
        if (isset($queries[ilWebAccessChecker::REVALIDATE])) {
            $this->ilWebAccessChecker->setRevalidateFolderTokens($queries[ilWebAccessChecker::REVALIDATE]);
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


    protected function deny()
    {
        if (!$this->ilWebAccessChecker->isChecked()) {
            throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
        }
        throw new ilWACException(ilWACException::ACCESS_DENIED);
    }


    protected function deliverDummyImage()
    {
        $ilFileDelivery = new Delivery('./Services/WebAccessChecker/templates/images/access_denied.png', $this->http);
        $ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
        $ilFileDelivery->deliver();
    }


    protected function deliverDummyVideo()
    {
        $ilFileDelivery = new Delivery('./Services/WebAccessChecker/templates/images/access_denied.mp4', $this->http);
        $ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
        $ilFileDelivery->stream();
    }


    /**
     * @param ilWACException $e
     */
    protected function handleAccessErrors(ilWACException $e)
    {

        //1.5.2017 Http code needs to be 200 because mod_xsendfile ignores the response with an 401 code. (possible leak of web path via xsendfile header)
        $response = $this->http
            ->response()
            ->withStatus(200);

        $this->http->saveResponse($response);

        if ($this->ilWebAccessChecker->getPathObject()->isImage()) {
            $this->deliverDummyImage();
        }
        if ($this->ilWebAccessChecker->getPathObject()->isVideo()) {
            $this->deliverDummyVideo();
        }

        $this->ilWebAccessChecker->initILIAS();
    }


    /**
     * @param ilWACException $e
     * @throws ilWACException
     */
    protected function handleErrors(ilWACException $e)
    {
        $response = $this->http->response()
            ->withStatus(500);


        /**
         * @var \Psr\Http\Message\StreamInterface $stream
         */
        $stream = $response->getBody();
        $stream->write($e->getMessage());

        $this->http->saveResponse($response);
    }


    /**
     * @return void
     * @throws ilWACException
     */
    protected function deliver()
    {
        if (!$this->ilWebAccessChecker->isChecked()) {
            throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
        }

        $ilFileDelivery = new Delivery($this->ilWebAccessChecker->getPathObject()->getCleanURLdecodedPath(), $this->http);
        $ilFileDelivery->setCache(true);
        $ilFileDelivery->setDisposition($this->ilWebAccessChecker->getDisposition());
        if ($this->ilWebAccessChecker->getPathObject()->isStreamable()) { // fixed 0016468
            $ilFileDelivery->stream();
        } else {
            $ilFileDelivery->deliver();
        }
    }
}
