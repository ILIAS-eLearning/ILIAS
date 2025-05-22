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

namespace ILIAS\Export\ImportHandler\I\Schema;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportHandler\I\Schema\CollectionInterface as SchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as SchemaInterface;

interface FactoryInterface
{
    public function handler(): SchemaInterface;

    public function collection(): SchemaCollectionInterface;

    public function collectionFrom(
        XMLFileInterface $xml_file_handler,
        PathInterface $path_to_entities
    ): SchemaCollectionInterface;
}
