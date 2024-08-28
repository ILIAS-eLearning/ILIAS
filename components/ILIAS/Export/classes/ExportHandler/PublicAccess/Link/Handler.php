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

namespace ILIAS\Export\ExportHandler\PublicAccess\Link;

use ILIAS\Data\ReferenceId;
use ILIAS\Data\URI;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\HandlerInterface as ilExportHandlerPublicAccessLinkHandlerInterface;
use ILIAS\Export\ExportHandler\StaticUrlHandler as ilExportHandlerStaticUrlHandler;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Handler\BaseHandler;
use ILIAS\StaticURL\Handler\Handler as StaticURLHandler;
use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Response\Response;
use ILIAS\StaticURL\Services as StaticUrl;

class Handler extends BaseHandler implements ilExportHandlerPublicAccessLinkHandlerInterface, StaticURLHandler
{
    protected StaticURL $static_url;
    protected ReferenceId $reference_id;

    public function __construct()
    {
    }

    public function withStaticUrl(StaticURL $static_url): ilExportHandlerPublicAccessLinkHandlerInterface
    {
        $clone = clone $this;
        $clone->static_url = $static_url;
        return $clone;
    }

    public function withReferenceId(ReferenceId $referenceId): ilExportHandlerPublicAccessLinkHandlerInterface
    {
        $clone = clone $this;
        $clone->reference_id = $referenceId;
        return $clone;
    }

    public function getReferenceId(): ReferenceId
    {
        return $this->reference_id;
    }

    public function getLink(): URI
    {
        return $this->static_url->builder()->build(
            ilExportHandlerStaticUrlHandler::NAMESPACE,
            $this->reference_id,
            [ilExportHandlerStaticUrlHandler::DOWNLOAD]
        );
    }

    public function getNamespace(): string
    {
        return ilExportHandlerStaticUrlHandler::NAMESPACE;
    }

    public function handle(Request $request, Context $context, Factory $response_factory): Response
    {
        return (new ilExportHandlerStaticUrlHandler())->handle($request, $context, $response_factory);
    }
}
