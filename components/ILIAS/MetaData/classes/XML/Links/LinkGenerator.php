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

namespace ILIAS\MetaData\XML\Copyright\Links;

use ILIAS\StaticURL\Services as URLService;
use ILIAS\Data\URI;
use ILIAS\Data\ReferenceId;

class LinkGenerator implements LinkGeneratorInterface
{
    protected URLService $url_service;

    public function __construct(URLService $url_service)
    {
        $this->url_service = $url_service;
    }

    public function generateLinkForReference(
        ReferenceId $ref_id,
        string $type
    ): URI {
        return $this->url_service->builder()->build($type, $ref_id);
    }
}
