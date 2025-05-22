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

namespace ILIAS\Init\Environment;

final class HttpPathBuilder
{
    /**
     * @param array<string, mixed>|\ArrayAccess<string, mixed> $server_data
     */
    public function __construct(
        private readonly \ILIAS\Data\Factory $df,
        private readonly \ilSetting $settings,
        private readonly \ilHTTPS $https,
        private readonly \ilIniFile $ini,
        private readonly array|\ArrayAccess $server_data
    ) {
    }

    public function build(): \ILIAS\Data\URI
    {
        $protocol = 'http://';
        if ($this->https->isDetected()) {
            $protocol = 'https://';
        }
        $host = $this->server_data['HTTP_HOST'];
        $request_uri = strip_tags($this->server_data['REQUEST_URI']);

        // security fix: this failed, if the URI contained "?" and following "/"
        // -> we remove everything after "?"
        if (\is_int($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        if (\defined('ILIAS_MODULE')) {
            // if in module remove module name from HTTP_PATH
            $path = \dirname($request_uri);

            // dirname cuts the last directory from a directory path e.g content/classes return content
            $module = \ilFileUtils::removeTrailingPathSeparators(ILIAS_MODULE);

            $dirs = explode('/', $module);
            $uri = $path;
            $uri = \dirname($uri, \count($dirs));
        } else {
            $path = pathinfo($request_uri);
            if (($path['extension'] ?? '') !== '') {
                $uri = \dirname($request_uri);
            } else {
                $uri = $request_uri;
            }
        }

        $ilias_http_path = \ilContext::modifyHttpPath(implode('', [$protocol, $host, $uri]));

        // remove everything after the first .php in the path
        $ilias_http_path = preg_replace('@(http|https)(://)(.*?/.*?\.php).*@', '$1$2$3', $ilias_http_path);
        $ilias_http_path = preg_replace('@goto.php/$@', '', $ilias_http_path);
        $ilias_http_path = preg_replace('/goto.php$/', '', $ilias_http_path);
        $ilias_http_path = preg_replace('@go/.*$@', '', $ilias_http_path);

        $uri = $this->df->uri(\ilFileUtils::removeTrailingPathSeparators($ilias_http_path));

        $ini_uri = $this->df->uri($this->ini->readVariable('server', 'http_path'));
        $allowed_hosts = [
            'localhost',
            $ini_uri->getHost()
        ];

        if ($this->settings->get('soap_wsdl_path')) {
            $soap_wsdl_uri = $this->df->uri($this->settings->get('soap_wsdl_path'));
            $allowed_hosts = array_merge(
                [$soap_wsdl_uri->getHost()],
                $allowed_hosts
            );
        }

        $allowed_hosts = array_merge(
            array_filter(explode(',', $this->settings->get('allowed_hosts', ''))),
            $allowed_hosts
        );

        if (!\in_array($uri->getHost(), $allowed_hosts, true)) {
            throw new \RuntimeException('Request rejected, the given HTTP host is not in the "allowed_hosts" list');
        }

        return $uri;
    }
}
