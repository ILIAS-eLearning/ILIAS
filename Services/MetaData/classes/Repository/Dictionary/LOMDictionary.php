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

namespace ILIAS\MetaData\Repository\Dictionary;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Structure\Dictionaries\Dictionary as BaseDictionary;

use function PHPUnit\Framework\throwException;

class LOMDictionary extends BaseDictionary implements DictionaryInterface
{
    public function tagForElement(
        BaseElementInterface $element
    ): ?TagInterface {
        foreach (parent::tagsForElement($element) as $tag) {
            if (!($tag instanceof TagInterface)) {
                throw new \ilMDRepositoryException('Invalid dictionary');
            }
            return $tag;
        }
        return null;
    }
}
