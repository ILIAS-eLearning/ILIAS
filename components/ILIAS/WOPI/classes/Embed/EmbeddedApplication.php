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

namespace ILIAS\WOPI\Embed;

use ILIAS\Data\URI;
use ILIAS\WOPI\Discovery\Action;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\WOPI\Handler\RequestHandler;
use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\WOPI\Discovery\ActionTarget;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EmbeddedApplication
{
    /**
     * @var string
     */
    private const WOPI_SRC = 'WOPISrc';
    private const SUB_THM = 'thm';
    private const SUB_DCHAT = 'dchat';
    private const SUB_EMBED = 'embed';
    private int $ttl = 3600 * 8;
    private string $token;
    private array $substitutions = [
        self::SUB_THM => 1,
        self::SUB_DCHAT => 0,
        self::SUB_EMBED => false
    ];
    private URI $ilias_base_url;

    public function __construct(
        protected ResourceIdentification $identification,
        protected ?Action $action,
        protected ResourceStakeholder $stakeholder,
        protected URI $back_target,
        protected bool $inline = false,
        ?string $ui_language = null
    ) {
        global $DIC;
        /** @var DataSigner $data_signer */
        $data_signer = $DIC['file_delivery.data_signer'];
        $this->ilias_base_url = new URI(ILIAS_HTTP_PATH);
        $editable = $this->action?->getName() === ActionTarget::EDIT->value;

        $payload = [
            'resource_id' => $this->identification->serialize(),
            'user_id' => $DIC->user()->getId(),
            'stakeholder' => $this->stakeholder::class,
            'editable' => $editable
        ];

        $ui_language ??= 'en_US';
        $this->substitutions['lang'] = $ui_language;
        $this->substitutions['rs'] = $ui_language;
        $this->substitutions['ui'] = $ui_language;
        $this->substitutions[self::SUB_EMBED] = !$editable;
        $this->token = $data_signer->sign($payload, 'wopi', new \DateTimeImmutable("now + $this->ttl seconds"));
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

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function getActionLauncherURL(): ?URI
    {
        if ($this->action === null) {
            return null;
        }

        $appendices = $this->getAppendices();

        $url = rtrim((string) $this->action->getLauncherUrl(), '/?#')
            . '?'
            . self::WOPI_SRC
            . '='
            . urlencode(
                rtrim((string) $this->ilias_base_url, '/')
                . RequestHandler::WOPI_BASE_URL
                . RequestHandler::NAMESPACE_FILES
                . '/'
                . $this->identification->serialize()
            );

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

                $appendices = array_filter($appendices, static fn(array $appendix): bool => isset($appendix[1], $appendix[2]));

                // we set the wopisrc ourselves
                $appendices = array_filter($appendices, static fn(array $appendix): bool => strtolower($appendix[1]) !== 'wopisrc');

                // try substitutions
                $appendices = array_map(function (array $appendix): array {
                    $key = strtolower($appendix[1]);
                    if (isset($this->substitutions[$key])) {
                        $appendix[2] = (string) $this->substitutions[$key];
                    }
                    return $appendix;
                }, $appendices);

                // we remove all those placeholders
                $appendices = array_filter($appendices, static fn(array $appendix): bool => $appendix[0] !== $appendix[1] . '=' . $appendix[2]);

                $here = 1;

                $appendices = array_map(static fn(array $appendix): string => $appendix[1] . '=' . $appendix[2], $appendices);
            }
        } catch (\Throwable $t) {
            return [];
        }

        return $appendices;
    }
}
