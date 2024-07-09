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

namespace ILIAS;

class UI implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Button/button.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Chart/Bar/dist/bar.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Core/dist/core.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Counter/dist/counter.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Dropdown/dist/dropdown.js");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("dropzone/dist/min/dropzone.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Dropzone/File/dropzone.js");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Image/dist/image.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Container/dist/filter.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/dist/input.factory.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/dynamic_inputs_renderer.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/file.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/groups.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/input.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/tagInput.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Item/dist/notification.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/dist/mainbar.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/dist/maincontrols.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/system_info.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Menu/dist/drilldown.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Modal/dist/modal.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Page/stdpage.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Popover/popover.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Table/dist/table.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Toast/toast.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Tree/tree.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/ViewControl/dist/viewcontrols.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "images", "assets");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "fonts", "assets");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "ui-examples", "assets");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("@yaireo/tagify/dist/tagify.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("@yaireo/tagify/dist/tagify.css");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("chart.js/dist/chart.umd.js");
        /*
        those are contributed by MediaObjects
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("mediaelement/build/mediaelement-and-player.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("./node_modules/mediaelement/build/mediaelementplayer.min.css");
        */
        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("mediaelement/build/renderers/vimeo.min.js");
        */
        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("webui-popover/dist/jquery.webui-popover.min.js");
        */


        // This is included via anonymous classes as a testament to the fact, that
        // the templates-folder should probably be moved to some component.
        $contribute[Component\Resource\PublicAsset::class] = fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "templates/default/delos.css";
            }
            public function getTarget(): string
            {
                return "assets/css/delos.css";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "templates/default/delos_cont.css";
            }
            public function getTarget(): string
            {
                return "assets/css/delos_cont.css";
            }
        };
    }
}
