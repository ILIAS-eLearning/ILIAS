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

declare(strict_types=1);

namespace ILIAS\Repository\HTTP;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP;
use ILIAS\FileDelivery\Delivery;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class HTTPUtil
{
    protected HTTP\Services $http;

    public function __construct(HTTP\Services $http)
    {
        $this->http = $http;
    }

    public function sendString(string $output): void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function deliverString(
        string $data,
        string $filename,
        string $mime = "application/octet-stream"
    ): void {
        $delivery = new Delivery(
            Delivery::DIRECT_PHP_OUTPUT,
            $this->http
        );
        $delivery->setMimeType($mime);
        $delivery->setSendMimeType(true);
        $delivery->setDisposition(Delivery::DISP_ATTACHMENT);
        $delivery->setDownloadFileName($filename);
        $delivery->setConvertFileNameToAsci(true);
        $repsonse = $this->http->response()->withBody(Streams::ofString($data));
        $this->http->saveResponse($repsonse);
        $delivery->deliver();
    }
}
