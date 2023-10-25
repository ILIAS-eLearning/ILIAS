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

namespace ILIAS\FileDelivery\Delivery;

use ILIAS\FileDelivery\Token\Data\Stream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class LegacyDelivery extends BaseDelivery
{
    public function attached(
        string $path_to_file,
        ?string $download_file_name = null,
        ?string $mime_type = null,
        ?bool $delete_file = false
    ): never {
        $this->deliver(
            $path_to_file,
            Disposition::ATTACHMENT,
            $download_file_name,
            $mime_type,
            $delete_file
        );
    }

    public function inline(
        string $path_to_file,
        ?string $download_file_name = null,
        ?string $mime_type = null,
        ?bool $delete_file = false
    ): never {
        $this->deliver(
            $path_to_file,
            Disposition::INLINE,
            $download_file_name,
            $mime_type,
            $delete_file
        );
    }

    protected function deliver(
        string $path_to_file,
        Disposition $disposition,
        ?string $download_file_name = null,
        ?string $mime_type = null,
        ?bool $delete_file = false
    ): never {
        $r = $this->setGeneralHeaders(
            $this->http->response(),
            $path_to_file,
            $mime_type ?? mime_content_type($path_to_file),
            $download_file_name ?? basename($path_to_file),
            $disposition
        );
        $r = $this->response_builder->buildForStream(
            $r,
            Streams::ofResource(fopen($path_to_file, 'rb'))
        );
        $this - $this->saveAndClose($r, $delete_file ? $path_to_file : null);
    }
}
