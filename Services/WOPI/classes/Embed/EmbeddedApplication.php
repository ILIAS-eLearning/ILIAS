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

namespace ILIAS\Services\WOPI\Embed;

use ILIAS\Data\URI;
use ILIAS\Services\WOPI\Discovery\Action;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Services\WOPI\Handler\RequestHandler;
use ILIAS\FileDelivery\Token\DataSigner;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddedApplication
{
    private const WOPI_SRC = 'WOPISrc';
    private int $ttl = 3600;
    private URI $ilias_base_url;
    private string $token;

    public function __construct(
        protected ResourceIdentification $identification,
        protected Action $action,
        protected ResourceStakeholder $stakeholder,
        protected URI $back_target,
        ?URI $ilias_base_url = null
    ) {
        global $DIC;
        /** @var DataSigner $data_signer */
        $data_signer = $DIC['file_delivery.data_signer'];

        $payload = [
            'resource_id' => $this->identification->serialize(),
            'user_id' => $DIC->user()->getId(),
        ];
        $this->token = $data_signer->sign($payload, 'wopi', new \DateTimeImmutable("now + $this->ttl seconds"));
        $this->ilias_base_url = $ilias_base_url ?? new URI(ILIAS_HTTP_PATH);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTTL(): int
    {
        return $this->ttl;
    }

    public function getBackTarget(): URI
    {
        return $this->back_target;
    }

    public function getActionLauncherURL(): URI
    {
        $url = rtrim((string) $this->action->getLauncherUrl(), '/?#')
            . '?'
            . self::WOPI_SRC
            . '='
            . rtrim((string) $this->ilias_base_url, '/')
            . RequestHandler::WOPI_BASE_URL
            . RequestHandler::NAMESPACE_FILES
            . '/'
            . $this->identification->serialize();

        return new URI($url);
    }
}
