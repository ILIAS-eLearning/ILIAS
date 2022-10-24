<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\FileDelivery\Delivery;
use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Services;

/**
 * Class ilWebAccessCheckerDelivery
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWebAccessCheckerDelivery
{
    private ilWebAccessChecker $wac;
    private Services $http;


    public static function run(Services $httpState, CookieFactory $cookieFactory): void
    {
        $obj = new self($httpState, $cookieFactory);
        $obj->handleRequest();
    }


    /**
     * ilWebAccessCheckerDelivery constructor.
     */
    public function __construct(Services $httpState, CookieFactory $cookieFactory)
    {
        $this->wac = new ilWebAccessChecker($httpState, $cookieFactory);
        $this->http = $httpState;
    }


    protected function handleRequest(): void
    {
        // Set errorreporting
        ilInitialisation::handleErrorReporting();
        $queries = $this->http->request()->getQueryParams();

        // Set customizing
        if (isset($queries[ilWebAccessChecker::DISPOSITION])) {
            $this->wac->setDisposition($queries[ilWebAccessChecker::DISPOSITION]);
        }
        if (isset($queries[ilWebAccessChecker::STATUS_CODE])) {
            $this->wac->setSendStatusCode($queries[ilWebAccessChecker::STATUS_CODE]);
        }
        if (isset($queries[ilWebAccessChecker::REVALIDATE])) {
            $this->wac->setRevalidateFolderTokens($queries[ilWebAccessChecker::REVALIDATE]);
        }

        // Check if File can be delivered
        try {
            if ($this->wac->check()) {
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

    /**
     * @throws ilWACException
     */
    protected function deny(): void
    {
        if (!$this->wac->isChecked()) {
            throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
        }
        throw new ilWACException(ilWACException::ACCESS_DENIED);
    }


    protected function deliverDummyImage(): void
    {
        $ilFileDelivery = new Delivery('./Services/WebAccessChecker/templates/images/access_denied.png', $this->http);
        $ilFileDelivery->setDisposition($this->wac->getDisposition());
        $ilFileDelivery->deliver();
    }


    protected function deliverDummyVideo(): void
    {
        $ilFileDelivery = new Delivery('./Services/WebAccessChecker/templates/images/access_denied.mp4', $this->http);
        $ilFileDelivery->setDisposition($this->wac->getDisposition());
        $ilFileDelivery->stream();
    }


    protected function handleAccessErrors(ilWACException $e): void
    {

        //1.5.2017 Http code needs to be 200 because mod_xsendfile ignores the response with an 401 code. (possible leak of web path via xsendfile header)
        $response = $this->http
            ->response()
            ->withStatus(200);

        $this->http->saveResponse($response);

        if ($this->wac->getPathObject()->isImage()) {
            $this->deliverDummyImage();
        }
        if ($this->wac->getPathObject()->isVideo()) {
            $this->deliverDummyVideo();
        }

        $this->wac->initILIAS();
    }


    /**
     * @throws ilWACException
     */
    protected function handleErrors(ilWACException $e): void
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
     * @throws ilWACException
     */
    protected function deliver(): void
    {
        if (!$this->wac->isChecked()) {
            throw new ilWACException(ilWACException::ACCESS_WITHOUT_CHECK);
        }

        $ilFileDelivery = new Delivery($this->wac->getPathObject()->getCleanURLdecodedPath(), $this->http);
        $ilFileDelivery->setCache(true);
        $ilFileDelivery->setDisposition($this->wac->getDisposition());
        if ($this->wac->getPathObject()->isStreamable()) { // fixed 0016468
            $ilFileDelivery->stream();
        } else {
            $ilFileDelivery->deliver();
        }
    }
}
