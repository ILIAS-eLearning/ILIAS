<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements C\Panel\Secondary\Factory
{
    /**
     * @inheritdoc
     */
    public function listing(string $title, array $item_groups) : C\Panel\Secondary\Listing
    {
        return new Listing($title, $item_groups);
    }

    /**
     * @inheritdoc
     */
    public function legacy(string $title, C\Legacy\Legacy $legacy) : C\Panel\Secondary\Legacy
    {
        return new Legacy($title, $legacy);
    }
}
