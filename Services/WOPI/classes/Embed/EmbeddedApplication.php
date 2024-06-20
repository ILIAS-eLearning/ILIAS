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
            'stakeholder' => $this->stakeholder::class
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
        $appendices = $this->getAppendices();

        $url = rtrim((string) $this->action->getLauncherUrl(), '/?#')
            . '?'
            . self::WOPI_SRC
            . '='
            . urlencode(rtrim((string) $this->ilias_base_url, '/')
                        . RequestHandler::WOPI_BASE_URL
                        . RequestHandler::NAMESPACE_FILES
                        . '/'
                        . $this->identification->serialize());

        if ($appendices !== []) {
            $url .= '&' . implode('&', $appendices);
        }

        return new URI($url);
    }

    /**
     * @return array|string[]
     */
    protected function getAppendices(): array
    {
        // appendix sanitizer
        $appendix = $this->action->getUrlAppendix();
        $appendices = [];
        try {
            if ($appendix !== null) {
                preg_match_all('/([^<]*)=([^>&]*)/m', $appendix, $appendices, PREG_SET_ORDER, 0);

                $appendices = array_filter($appendices, static function ($appendix) {
                    return isset($appendix[1], $appendix[2]);
                });

                // we set the wopisrc ourselves
                $appendices = array_filter($appendices, static function ($appendix) {
                    return strtolower($appendix[1]) !== 'wopisrc';
                });

                // we remove all those placeholders
                $appendices = array_filter($appendices, static function ($appendix) {
                    return !preg_match('/([A-Z\_]*)/m', $appendix[2]);
                });

                $appendices = array_map(static function ($appendix) {
                    return $appendix[1] . '=' . $appendix[2];
                }, $appendices);
            }
        } catch (\Throwable $t) {
            return $appendices;
        }

        return $appendices;
    }
}
