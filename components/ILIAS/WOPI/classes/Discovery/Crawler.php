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

namespace ILIAS\Services\WOPI\Discovery;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Crawler
{
    private const XPATH = '/wopi-discovery/net-zone/app';
    private array $crawl_actions = []
    ;
    private ?string $content = null;
    private ?\SimpleXMLElement $discovery = null;
    /**
     * @var \SimpleXMLElement[]|null
     */
    private ?array $xml_app_elements = null;

    public function __construct()
    {
        $this->crawl_actions = [
            ActionTarget::VIEW->value,
            ActionTarget::EDIT->value,
        ];
    }

    public function validate(URI $discovery_url): bool
    {
        try {
            $this->content = file_get_contents((string) $discovery_url) ?: null;
            if ($this->content === null) {
                return false;
            }

            $this->discovery = simplexml_load_string($this->content) ?: null;
            if ($this->discovery === null) {
                return false;
            }
            $this->xml_app_elements = $this->discovery->xpath(self::XPATH);

            return is_array($this->xml_app_elements);
        } catch (\Throwable $t) {
            return false;
        }
    }

    public function crawl(URI $discovery_url): ?Apps
    {
        if (!$this->validate($discovery_url)) {
            return null;
        }

        // read wopi-discovery XML from $discovery_url and parse Apps with it's Actions
        $apps = [];
        foreach ($this->xml_app_elements as $app) {
            $actions = [];
            foreach ($app->action as $action) {
                $action_name = $action['name'] ?? null;
                $action_ext = $action['ext'] ?? null;
                $action_urlsrc = $action['urlsrc'] ?? null;
                if (!$action_name instanceof \SimpleXMLElement) {
                    continue;
                }
                if (!$action_ext instanceof \SimpleXMLElement) {
                    continue;
                }
                if (!$action_urlsrc instanceof \SimpleXMLElement) {
                    continue;
                }

                if (!in_array((string) $action_name, $this->crawl_actions, true)) {
                    continue;
                }

                $uri_string = rtrim((string) $action_urlsrc, '?');
                // remove all after ?
                $uri_string = explode('?', $uri_string)[0];
                $actions[] = new Action(
                    0,
                    (string) $action_name,
                    (string) $action_ext,
                    new URI($uri_string)
                );
            }
            if ($actions === []) {
                continue;
            }

            $app_name = $app['name'] ?? null;
            if ($app_name === null) {
                continue;
            }
            $app_fav_icon_url = $app['favIconUrl'] ?? null;
            $apps[] = new App(
                0,
                (string) $app_name,
                $actions,
                $app_fav_icon_url === null ? null : new URI((string) $app_fav_icon_url)
            );
        }
        return new Apps($apps);
    }
}
