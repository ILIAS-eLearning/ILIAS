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

namespace ILIAS\Export\ImportHandler\File\Namespace;

use ILIAS\Export\ImportHandler\File\Namespace\Collection as FileNamespaceCollection;
use ILIAS\Export\ImportHandler\File\Namespace\Handler as FileNamespaceHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\CollectionInterface as FileNamespaceCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\HandlerInterface as FileNamespaceHandlerInterface;

class Factory implements FileNamespaceFactoryInterface
{
    public function handler(): FileNamespaceHandlerInterface
    {
        return new FileNamespaceHandler();
    }

    public function collection(): FileNamespaceCollectionInterface
    {
        return new FileNamespaceCollection();
    }
}
