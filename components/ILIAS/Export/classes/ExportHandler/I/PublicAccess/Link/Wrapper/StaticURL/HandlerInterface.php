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

namespace ILIAS\Export\ExportHandler\I\PublicAccess\Link\Wrapper\StaticURL;

use ILIAS\Data\ReferenceId;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Services as StaticUrl;

interface HandlerInterface
{
    public function withStaticURL(
        StaticUrl $static_url
    ): HandlerInterface;

    public function getStatucURL(): StaticUrl;

    public function buildDownloadURI(
        ReferenceId $reference_id
    ): URI;
}
