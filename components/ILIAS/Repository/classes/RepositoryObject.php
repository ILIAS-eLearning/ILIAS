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

namespace ILIAS\Repository;

class RepositoryObject extends \ilPlugin
{
    public function __construct(
        protected string $id,
        protected string $name,
        protected $parent_types = ["root", "cat", "crs", "grp", "fold"],
        protected bool $allow_copy = false,
        protected bool $use_orgu_permissions = false,
        protected bool $supports_lp = false,
        protected bool $supports_export = false,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClassName(): string
    {
        return static::class;
    }
    public function getConfigGUIClassName(): string
    {
        return 'il' . $this->getName() . 'ConfigGUI';
    }

    public function getPath(): string
    {
        $loader = require dirname(__DIR__, 4) . '/vendor/composer/vendor/autoload.php';
        $plugin_file = $loader->findFile($this->getClassname());
        if (!$plugin_file) {
            throw new \Exception('file not found in autoloader: ' . $this->getClassName());
        }
        return realpath(dirname($plugin_file) . '/..');
    }

    public function getPrefix(): string
    {
        return $this->id;
    }

    public function getTemplate(string $template, bool $par1 = true, bool $par2 = true): \ilTemplate
    {
        return new \ilTemplate(
            $template,
            $par1,
            $par2,
            $this->getPath()
        );
    }

    protected function buildLanguageHandler(): \ilPluginLanguage
    {
        $lng = new \ilPluginLanguage(
            $this->getId(),
            $this->getPath()
        );
        //$lng->updateLanguages();
        return $lng;
    }

    /**
     * @return string[]
     */
    public function getParentTypes(): array
    {
        return $this->parent_types;
    }
    public function allowCopy(): bool
    {
        return $this->allow_copy;
    }
    public function useOrguPermissions(): bool
    {
        return $this->use_orgu_permissions;
    }
    public function supportsLearningProgress(): bool
    {
        return $this->supports_lp;
    }
    public function supportsExport(): bool
    {
        return $this->supports_export;
    }

}
