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

namespace ILIAS\Authentication\Setup;

use ILIAS\FileDelivery\Setup\BuildStaticConfigStoredObjective;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Environment;

/**
 * Creates the secret key used to encrypt the session reminder tokens. The key is stored in the
 * (shared) data directory, so all web servers of a multi-server setup use the same key.
 *
 * An existing key is never replaced. To renew the key, delete the file and run the setup again.
 */
class SessionReminderKeyCreatedObjective extends BuildStaticConfigStoredObjective
{
    public const string KEY = 'key';
    private const int KEY_LENGTH_IN_BYTES = 32;

    public function getArtifactName(): string
    {
        return 'session_reminder_key';
    }

    public function getLabel(): string
    {
        return 'Create the encryption key of the session reminder';
    }

    public function build(): Artifact
    {
        return new ArrayArtifact([
            self::KEY => bin2hex(random_bytes(self::KEY_LENGTH_IN_BYTES)),
        ]);
    }

    public function isApplicable(Environment $environment): bool
    {
        return !is_file(self::PATH());
    }
}
