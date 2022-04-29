<?php declare(strict_types=1);

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
 
namespace ILIAS\Setup\Objective;

use ILIAS\Setup;

/**
 * Read the client id of the installation from the data directory.
 *
 * ATTENTION: This might be placed better in some service, rather then being located
 * here in the Setup-library. Currently I don't know where, though. Maybe we also
 * might be able to remove this altogether if the multi-client code has been removed.
 */
class ClientIdReadObjective implements Setup\Objective
{
    /**
     * Uses hashed Path.
     *
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        return "Read client-id from data-directory.";
    }

    /**
     * Defaults to 'true'.
     *
     * @inheritdocs
     */
    public function isNotable() : bool
    {
        return false;
    }

    /**
     * @inheritdocs
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [];
    }

    /**
     * @inheritdocs
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $dir = $this->getDataDirectoryPath();
        $candidates = array_filter(
            $this->scanDirectory($dir),
            function ($c) use ($dir) : bool {
                if ($c == "." || $c == "..") {
                    return false;
                }
                return $this->isDirectory($dir . "/" . $c);
            }
        );

        if (count($candidates) == 0) {
            throw new Setup\UnachievableException(
                "There are no directories in the webdata-dir at '$dir'. " .
                "Probably ILIAS is not installed."
            );
        }

        if (count($candidates) != 1) {
            $ilias_version = ILIAS_VERSION_NUMERIC;

            throw new Setup\UnachievableException(
                "There is more than one directory in the webdata-dir at '$dir'. " .
                "Probably this is an ILIAS installation that uses clients. Clients " .
                "are not supported anymore since ILIAS $ilias_version " .
                "(see: https://docu.ilias.de/goto.php?target=wiki_1357_Setup_-_Abandon_Multi_Client)"
            );
        }

        $client_id = array_shift($candidates);
        return $environment->withResource(Setup\Environment::RESOURCE_CLIENT_ID, $client_id);
    }

    protected function getDataDirectoryPath() : string
    {
        return dirname(__DIR__, 3) . "/data";
    }

    protected function scanDirectory(string $path) : array
    {
        return scandir($path);
    }

    protected function isDirectory(string $path) : bool
    {
        return is_dir($path);
    }
 
    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID) === null;
    }
}
