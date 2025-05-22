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

/**
 * This class forms an interface between the existing ILIAS mail contexts and the requirements of Mustache.
 * In the old system, it was possible to gradually replace individual placeholders via the contexts.
 * This is now done by Mustache and requires a single source. If a placeholder does not exist in this source,
 * then it will be replaced with NULL. Mustache takes two steps to find the placeholder.
 * On the one hand the check whether it would be theoretically possible and the actual query for the value
 * if the check is successful. This is done in this class using the two magic methods.
 * With the introduction of this interface, the ILIAS mail contexts do not have to be changed for Mustache.
 */
class ilMailTemplateContextAdapter
{
    public function __construct(
        /** @var ilMailTemplateContext[] $contexts */
        protected array $contexts,
        protected array $context_parameter,
        protected ?ilObjUser $recipient = null
    ) {
    }

    public function withContext(ilMailTemplateContext $context): self
    {
        $clone = clone $this;
        $clone->contexts[] = $context;
        return $clone;
    }

    public function __isset(string $name): bool
    {
        foreach ($this->contexts as $context) {
            $possible_placeholder = array_map(
                static function ($placeholder): string {
                    return strtoupper($placeholder);
                },
                array_keys($context->getPlaceholders())
            );
            if (in_array($name, $possible_placeholder, true)) {
                return true;
            }
        }

        return false;
    }

    public function __get(string $name): string
    {
        foreach ($this->contexts as $context) {
            $ret = $context->resolvePlaceholder($name, $this->context_parameter, $this->recipient);
            if ($ret !== '') {
                return $ret;
            }
        }

        return '';
    }
}
