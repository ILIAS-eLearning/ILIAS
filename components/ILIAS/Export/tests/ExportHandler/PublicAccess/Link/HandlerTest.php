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

namespace ILIAS\Export\Test\ExportHandler\PublicAccess\Link;

use Exception;
use ILIAS\Data\ReferenceId;
use ILIAS\Data\URI;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\Wrapper\StaticURL\HandlerInterface as ilExportHandlerPublicAccessLinkStaticURLWrapperInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Link\Handler as ilExportHandlerPublicAccessLink;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerPublicAccessLink(): void
    {
        $download_url = "super/url/1/download";
        $reference_id = 1;
        $uri_mock = $this->createMock(Uri::class);
        $uri_mock->expects($this->once())->method("__toString")->willReturn($download_url);
        $reference_id_mock = $this->createMock(ReferenceId::class);
        $reference_id_mock->method("toInt")->willReturn($reference_id);
        $reference_id_mock->method("toObjectId")->willThrowException(new Exception("unexpected conversion to object id"));
        $static_url_wrapper_mock = $this->createMock(ilExportHandlerPublicAccessLinkStaticURLWrapperInterface::class);
        $static_url_wrapper_mock->method("withStaticURL")->willThrowException(new Exception("unexpected overwrite of static URL service object"));
        $static_url_wrapper_mock->method("buildDownloadURI")->willReturn($uri_mock);
        try {
            $link = new ilExportHandlerPublicAccessLink();
            $link = $link
                ->withReferenceId($reference_id_mock)
                ->withStaticURLWrapper($static_url_wrapper_mock);
            self::assertEquals($download_url, $link->getLink());
            self::assertEquals($reference_id, $link->getReferenceId()->toInt());
        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
