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

namespace ILIAS\WebDAV\Setup;

use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\FileDelivery\Setup\BuildStaticConfigStoredObjective;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class KeyRotationObjective extends BuildStaticConfigStoredObjective
{
    private const int KEY_LENGTH = 32;
    private const int NUMBER_OF_KEYS = 5;

    public function getArtifactName(): string
    {
        return "webdav_key_rotation";
    }

    public function build(): Artifact
    {
        $current_keys = [];
        if (is_readable(self::PATH())) {
            /** @var array $current_keys */
            $current_keys = require self::PATH();
        }

        $new_keys = [];
        // push one new key to the beginning, drop the oldest key until we have 5 keys
        for ($i = 0; $i < self::NUMBER_OF_KEYS - 1; $i++) {
            if ($i === 0) {
                $new_keys[] = $this->generateRandomString(self::KEY_LENGTH);
            }
            $new_keys[] = $current_keys[$i] ?? $this->generateRandomString(self::KEY_LENGTH);
        }

        return new ArrayArtifact($new_keys);
    }

    private function generateRandomString(int $length): string
    {
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= chr(random_int(33, 125));
        }
        return $return;
    }
}
