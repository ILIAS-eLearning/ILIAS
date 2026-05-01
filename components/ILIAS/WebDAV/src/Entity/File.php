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

namespace ILIAS\WebDAV\Entity;

use Sabre\DAV\IFile;
use ILIAS\WebDAV\DataCheck;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class File extends BaseEntity implements IFile, Entity
{
    use DataCheck;

    private function generateEtag(mixed $data = null): ?string
    {
        try {
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }

            $data = $data ??
                $this->object_proxy !== null
                ? $this->object_proxy->getStreamHandler()->get()->getContents()
                : null; // maybe to much?

            if (empty($data)) {
                return null;
            }

            return '"' . md5((string) $data) . '"';
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function put(mixed $data): ?string
    {
        $title = $this->getName();
        $is_empty = $this->isEmpty($data);
        $this->getObjectProxy()
             ?->getStreamHandler()
             ?->put(
                 $title,
                 $data,
                 !$is_empty
             );

        return $this->generateEtag($data);
    }

    public function get(): mixed
    {
        return $this->getObjectProxy()?->getStreamHandler()?->get()?->detach();
    }

    public function getContentType(): ?string
    {
        return $this->object_proxy?->getContentType();
    }

    public function getETag(): ?string
    {
        return $this->generateEtag();
    }

    public function getSize(): ?int
    {
        return $this->object_proxy?->getSize();
    }

}
