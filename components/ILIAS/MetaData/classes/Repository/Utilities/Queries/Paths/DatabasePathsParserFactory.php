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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Paths;

use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;

class DatabasePathsParserFactory implements DatabasePathsParserFactoryInterface
{
    /**
     * Just for quoting.
     */
    protected \ilDBInterface $db;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        \ilDBInterface $db,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
    ) {
        $this->db = $db;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
    }

    public function forSearch(): DatabasePathsParserInterface
    {
        return new DatabasePathsParser(
            $this->db,
            $this->structure,
            $this->dictionary,
            $this->navigator_factory
        );
    }
}
