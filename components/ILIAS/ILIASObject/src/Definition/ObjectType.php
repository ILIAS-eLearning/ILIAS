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

namespace ILIAS\ILIASObject\Definition;

/**
 * Describes an object type. Every type is implemented by its component, which
 * contributes it via $contribute[ObjectType::class]. All types are collected
 * in the ObjectTypeRegistry. Implementations usually extend AbstractObjectType.
 */
interface ObjectType
{
    public function getId(): string;

    /**
     * Base of the class names of the type, e.g. "Blog" for ilObjBlog, ilObjBlogGUI, ...
     */
    public function getClassName(): string;

    /**
     * e.g. "components/ILIAS/Blog"
     */
    public function getComponent(): string;

    /**
     * Directory of the classes, e.g. "components/ILIAS/Blog/classes"
     */
    public function getLocation(): string;

    /**
     * @return ObjectTypeFlag[]
     */
    public function getFlags(): array;

    public function hasFlag(ObjectTypeFlag $flag): bool;

    /**
     * "0", "sys" or "db"
     */
    public function getTranslationType(): string;

    public function getGroup(): ?string;

    public function getDefaultPosition(): int;

    public function getDefaultPresentationPosition(): int;

    /**
     * @return array<string, int> type => max. number of objects (0 = unlimited)
     */
    public function getParentTypes(): array;

    /**
     * @return array<string, int> type => max. number of objects (0 = unlimited)
     */
    public function getSubObjectTypes(): array;

    /**
     * @return array<string, bool> sub type => supports advanced metadata
     */
    public function getSubTypes(): array;

    /**
     * Language module which contains the texts of the type (obj_<type>,
     * objs_<type>, <type>_add, ...). null if they are part of "common".
     */
    public function getLanguageModule(): ?string;

    /**
     * Path of the icon in the public folder, e.g. "assets/xdmo/icon_xdmo.svg".
     * null for types with a standard icon of the UI framework.
     */
    public function getIconPath(): ?string;

    /**
     * Only true for types provided by the adapter for repository object plugins.
     */
    public function isLegacyPlugin(): bool;
}
