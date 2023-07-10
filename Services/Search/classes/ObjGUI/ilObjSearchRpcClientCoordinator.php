<?php

declare(strict_types=1);
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

/**
 * @author Tim Schmitz <schmitz@leifos.com>
 */
class ilObjSearchRpcClientCoordinator
{
    protected ilSetting $settings;
    protected ilLogger $src_logger;

    public function __construct(
        ilSetting $settings,
        ilLogger $src_logger
    ) {
        $this->settings = $settings;
        $this->src_logger = $src_logger;
    }

    public function refreshLuceneSettings(): bool
    {
        try {
            $this->getRpcClient()->refreshSettings(
                $this->getClientId() .
                '_' .
                $this->settings->get('inst_id', '0')
            );
            return true;
        } catch (Exception $exception) {
            $this->src_logger->error(
                'Refresh of lucene server settings failed with message: ' .
                $exception->getMessage()
            );
            throw $exception;
        }
    }

    protected function getRpcClient(): ilRpcClient
    {
        return ilRpcClientFactory::factory('RPCAdministration');
    }

    protected function getClientId(): string
    {
        return (string) CLIENT_ID;
    }
}
