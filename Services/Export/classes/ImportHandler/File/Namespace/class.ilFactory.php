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

namespace ImportHandler\File\Namespace;

use ImportHandler\File\Namespace\ilCollection as ilParserNamespaceCollection;
use ImportHandler\File\Namespace\ilHandler as ilParserNamespaceHandler;
use ImportHandler\I\File\Namespace\ilCollectionInterface as ilParserNamespaceCollectionInterface;
use ImportHandler\I\File\Namespace\ilFactoryInterface as ilParserNamespaceFactoryInterface;
use ImportHandler\I\File\Namespace\ilHandlerInterface as ilParserNamespaceHandlerInterface;

class ilFactory implements ilParserNamespaceFactoryInterface
{
    public function handler(): ilParserNamespaceHandlerInterface
    {
        return new ilParserNamespaceHandler();
    }

    public function collection(): ilParserNamespaceCollectionInterface
    {
        return new ilParserNamespaceCollection();
    }
}
