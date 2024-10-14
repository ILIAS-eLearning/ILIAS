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

class Chatroom implements Component\Component
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
        $contribute[\ILIAS\Setup\Agent::class] = static fn() => new \ilChatroomSetupAgent($pull[\ILIAS\Refinery\Factory::class]);

        $files = [
            '../chat/node_modules/socket.io-client/dist/socket.io.min.js',
            'js/dist/Chatroom.min.js',
        ];

        $type = ['js' => Component\Resource\ComponentJS::class, 'css' => Component\Resource\ComponentCSS::class];

        foreach ($files as $file) {
            $class = $type[substr($file, strrpos($file, '.') + 1)];
            if (file_exists(__DIR__ . '/resources/' . $file)) {
                $contribute[Component\Resource\PublicAsset::class] = fn() => new $class($this, $file);
            }
        }
    }
}
