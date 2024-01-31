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

namespace ILIAS\Component\Resource;

/**
 * An public asset that is a resource of some component.
 */
class OfComponent implements PublicAsset
{
    public const REGEXP_SOURCE = '%^(((\w|.)+(/(\w|.)+)*(\.\w{2,4})?)|(\.htaccess))$%';
    public const REGEXP_TARGET = '%^(((\w|.)+(/(\w|.)+)*)|[.])$%';

    /**
     * @param $component this belongs to
     * @param $source path relative to the components resources directory
     * @param $target path relative to the ILIAS public directory, filename of resource will be appended. Use one dot for toplevel.
     */
    public function __construct(
        protected \ILIAS\Component\Component $component,
        protected string $source,
        protected string $target,
    ) {
        if (!preg_match(self::REGEXP_SOURCE, $this->source)) {
            throw new \InvalidArgumentException(
                "'{$this->source}' is not a valid source path for a public asset."
            );
        }
        if (!preg_match(self::REGEXP_TARGET, $this->target)) {
            throw new \InvalidArgumentException(
                "'{$this->target}' is not a valid target path for a public asset."
            );
        }
    }

    public function getSource(): string
    {
        list($vendor, $component) = explode("\\", get_class($this->component));

        return "components/$vendor/$component/resources/{$this->source}";
    }

    public function getTarget(): string
    {
        $source = explode("/", $this->source);
        if ($this->target === ".") {
            return array_pop($source);
        }
        return $this->target . "/" . array_pop($source);
    }
}
