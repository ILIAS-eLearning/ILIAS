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
 * Class ilMailTemplatePlaceholderResolver
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplatePlaceholderResolver
{
    protected Mustache_Engine $mustache_engine;

    public function __construct(Mustache_Engine $mustache_engine)
    {
        $this->mustache_engine = $mustache_engine;
    }

    /**
     * @param ilMailTemplateContext $context
     * @param string $message
     * @param ilObjUser|null $user
     * @param array $contextParameters
     * @param $replaceEmptyPlaceholders boolean
     * @return string
     */
    public function resolve(
        ilMailTemplateContext $context,
        string $message,
        ilObjUser $user = null,
        array $contextParameters = [],
        bool $replaceEmptyPlaceholders = true
    ): string {
        return $this->mustache_engine->render(
            $message,
            new ilMailTemplateContextAdapter(
                [$context],
                $contextParameters,
                $user,
                $replaceEmptyPlaceholders
            )
        );
    }
}
