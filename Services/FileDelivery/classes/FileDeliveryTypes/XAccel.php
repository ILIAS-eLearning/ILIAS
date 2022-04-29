<?php
declare(strict_types=1);

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\HTTP\Services;
use ILIAS\HTTP\Response\ResponseHeader;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class XAccel
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class XAccel implements ilFileDeliveryType
{
    use  HeaderBasedDeliveryHelper;
    const DATA = 'data';
    const SECURED_DATA = 'secured-data';
    private \ILIAS\HTTP\Services $httpService;
    const X_ACCEL_REDIRECT = 'X-Accel-Redirect';


    /**
     * PHP constructor.
     *
     * @param Services $httpState
     */
    public function __construct(Services $httpState)
    {
        $this->httpService = $httpState;
    }

    /**
     * @inheritDoc
     */
    public function doesFileExists(string $path_to_file) : bool
    {
        return is_readable($path_to_file);
    }



    /**
     * @inheritdoc
     */
    public function prepare(string $path_to_file) : bool
    {
        $response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_TYPE, '');

        $this->httpService->saveResponse($response);

        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete) : void
    {
        // There is currently no way to delete the file after delivery
        if (strpos($path_to_file, './' . self::DATA . '/') === 0) {
            $path_to_file = str_replace('./' . self::DATA . '/', '/' . self::SECURED_DATA
                                                                 . '/', $path_to_file);
        }

        $response = $this->httpService->response();
        $delivery = function () use ($path_to_file, $response) : void {
            $response = $response->withHeader(self::X_ACCEL_REDIRECT, $path_to_file);
            $this->httpService->saveResponse($response);
            $this->httpService->sendResponse();
        };

        if ($file_marked_to_delete) {
            $this->sendFileUnbufferedUsingHeaders($delivery);
        } else {
            $delivery();
        }
    }


    /**
     * @inheritdoc
     */
    public function supportsInlineDelivery() : bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsAttachmentDelivery() : bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsStreaming() : bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion(string $path_to_file) : bool
    {
        // No possibilities to do this at the moment
        return true;
    }
}
