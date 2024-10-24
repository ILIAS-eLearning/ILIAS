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

use ILIAS\Export\ImportHandler\File\Namespace\Collection as ParserNamespaceCollection;
use ILIAS\Export\ImportHandler\File\Namespace\Handler as ParserNamespaceHandler;
use ILIAS\Export\ImportHandler\I\File\Namespace\CollectionInterface as ParserNamespaceCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as ParserNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\HandlerInterface as ParserNamespaceHandlerInterface;

class Factory implements ParserNamespaceFactoryInterface
{
    public function handler(): ParserNamespaceHandlerInterface
    {
        return new ParserNamespaceHandler();
    }

    public function collection(): ParserNamespaceCollectionInterface
    {
        return new ParserNamespaceCollection();
    }
}
