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

namespace ILIAS\Poll\Image\I;

use ILIAS\Data\ObjectId;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\Revision;

interface HandlerInterface
{
    public function uploadImage(
        ObjectId $object_id,
        string $file_path,
        string $file_name,
        int $user_id
    ): void;

    public function cloneImage(
        ObjectId $original_object_id,
        ObjectId $clone_object_id,
        int $user_id
    ): void;

    public function deleteImage(
        ObjectId $object_id,
        int $user_id
    ): void;

    public function getThumbnailImageURL(
        ObjectId $object_id
    ): null|string;

    public function getProcessedImageURL(
        ObjectId $object_id
    ): null|string;

    public function getUnprocessedImageURL(
        ObjectId $object_id
    ): null|string;

    public function getRessource(
        ObjectId $object_id
    ): null|Revision;
}
