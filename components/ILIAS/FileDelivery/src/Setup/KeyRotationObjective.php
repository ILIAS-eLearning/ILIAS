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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class KeyRotationObjective extends BuildArtifactObjective
{
    public const KEY_ROTATION = './components/ILIAS/FileDelivery/src/artifacts/key_rotation.php';
    public const KEY_LENGTH = 32;
    private const NUMBER_OF_KEYS = 5;

    public function getArtifactPath(): string
    {
        return self::KEY_ROTATION;
    }

    public function build(): Setup\Artifact
    {
        $current_keys = null;
        if (is_readable(self::KEY_ROTATION)) {
            /** @var array $current_keys */
            $current_keys = require self::KEY_ROTATION;
        }

        $new_keys = [];

        if (is_array($current_keys)) {
            // drop the first key
            $current_keys = array_slice($current_keys, 1);
            $new_keys = $current_keys;
        }
        // $push a new key to the array at first position
        while (count($new_keys) < self::NUMBER_OF_KEYS) {
            $new_keys[] = $this->generateRandomString(self::KEY_LENGTH);
        }
        // keep only the first 5 keys
        $new_keys = array_slice($new_keys, 0, self::NUMBER_OF_KEYS);

        return new Setup\Artifact\ArrayArtifact($new_keys);
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
