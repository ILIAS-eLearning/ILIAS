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
 * Class PHPChunked
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class PHPChunked implements ilFileDeliveryType
{
    private \ILIAS\HTTP\Services $httpService;


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
        // nothing to do here
        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete) : void
    {
        $file = $path_to_file;
        $fp = @fopen($file, 'rb');

        $size = filesize($file); // File size
        $length = $size;           // Content length
        $start = 0;               // Start byte
        $end = $size - 1;       // End byte
        // Now that we've gotten so far without errors we send the accept range header
        /* At the moment we only support single ranges.
         * Multiple ranges requires some more work to ensure it works correctly
         * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
         *
         * Multirange support annouces itself with:
         * header('Accept-Ranges: bytes');
         *
         * Multirange content must be sent with multipart/byteranges mediatype,
         * (mediatype = mimetype)
         * as well as a boundry header to indicate the various chunks of data.
         */
        $response = $this->httpService->response()->withHeader("Accept-Ranges", "0-$length");
        $this->httpService->saveResponse($response);
        $server = $this->httpService->request()->getServerParams();
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($server['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;

            // Extract the range string
            [, $range] = explode('=', $server['HTTP_RANGE'], 2);
            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false) {
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                $response = $this->httpService->response()->withStatus(416)->withHeader(ResponseHeader::CONTENT_RANGE, "bytes $start-$end/$size");
                $this->httpService->saveResponse($response);

                //header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                $this->close();
            } // fim do if
            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range[0] === '-') {
                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            } // fim do if
            /* Check the range and make sure it's treated according to the specs.
             * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
             */
            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;
            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                $response = $this->httpService->response()->withStatus(416)->withHeader(ResponseHeader::CONTENT_RANGE, "bytes $start-$end/$size");

                $this->httpService->saveResponse($response);
                // (?) Echo some info to the client?
                $this->close();
            } // fim do if

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, (int) $start);

            $response = $this->httpService->response()->withStatus(206);

            $this->httpService->saveResponse($response);
        } // fim do if

        // Notify the client the byte range we'll be outputting
        $response = $this->httpService->response()->withHeader(ResponseHeader::CONTENT_RANGE, "bytes $start-$end/$size")->withHeader(ResponseHeader::CONTENT_LENGTH, $length);

        $this->httpService->saveResponse($response);

        //render response and start buffered download
        $this->httpService->sendResponse();

        // Start buffered download
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                // In case we're only outputtin a chunk, make sure we don't
                // read past the length
                $buffer = $end - $p + 1;
            } // fim do if

            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        } // fim do while

        fclose($fp);
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


    private function close() : void
    {
        //render response
        $this->httpService->sendResponse();
        exit;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion(string $path_to_file) : bool
    {
        return unlink($path_to_file);
    }
}
