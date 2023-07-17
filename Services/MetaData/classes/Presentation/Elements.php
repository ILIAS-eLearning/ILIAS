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

namespace ILIAS\MetaData\Presentation;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\PathInterface;

class Elements implements ElementsInterface
{
    protected const SEPARATOR = ': ';

    protected UtilitiesInterface $utilities;

    public function __construct(
        UtilitiesInterface $utilities,
    ) {
        $this->utilities = $utilities;
    }

    public function name(
        BaseElementInterface $element,
        bool $plural = false
    ): string {
        $name = $element->getDefinition()->name();
        $exceptions = [
            'metadataSchema' => 'metadatascheme', 'lifeCycle' => 'lifecycle',
            'otherPlatformRequirements' => 'otherPlattformRequirements'
        ];
        $name = $exceptions[$name] ?? $name;

        $lang_key = 'meta_' . $this->camelCaseToSnakeCase($name);
        if ($plural) {
            $lang_key .= '_plural';
        }
        return $this->utilities->txt($lang_key);
    }

    public function nameWithParents(
        BaseElementInterface $element,
        ?BaseElementInterface $cut_off = null,
        bool $plural = false,
        bool $skip_initial = false
    ): string {
        $names = [];
        $el = $element;

        $initial = true;
        while (!$el->isRoot()) {
            if ($el === $cut_off) {
                break;
            }
            if ($initial && $skip_initial) {
                $el = $el->getSuperElement();
                $initial = false;
                continue;
            }
            array_unshift($names, $this->name($el, $initial && $plural));
            $el = $el->getSuperElement();
            $initial = false;
        }
        if (empty($names)) {
            return $this->name($element, $plural);
        }
        return implode(self::SEPARATOR, $names);
    }

    protected function camelCaseToSnakeCase(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $string);
        return strtolower($string);
    }
}
