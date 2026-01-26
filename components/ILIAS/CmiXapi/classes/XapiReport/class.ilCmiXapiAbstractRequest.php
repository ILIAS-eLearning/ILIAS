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

/**
 * Class ilCmiXapiAbstractRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
abstract class ilCmiXapiAbstractRequest
{
    private string $basicAuth;
    public static bool $plugin = false;

    /**
     * ilCmiXapiAbstractRequest constructor.
     */
    public function __construct(string $basicAuth)
    {
        $this->basicAuth = $basicAuth;
    }

    protected function sendRequest(string $url): string
    {
        ilObjCmiXapi::log()->debug($url);

        // Header wie bei Guzzle
        $headers = [
            'Authorization: ' . $this->basicAuth,
            'X-Experience-API-Version: 1.0.3'
        ];

        // cURL initialisieren
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true
        ]);

        // Anfrage ausführen
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ch = null;

        // Fehlerbehandlung
        if ($error) {
            ilObjCmiXapi::log()->error("cURL error: " . $error);
            throw new Exception("LRS Connection Problems: " . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            ilObjCmiXapi::log()->error("Unexpected HTTP status: {$httpCode}");
            throw new Exception("LRS Connection Problems (HTTP {$httpCode})");
        }

        if ($body === false || $body === '') {
            ilObjCmiXapi::log()->error("Empty response body from LRS");
            throw new Exception("Empty response from LRS");
        }

        return $body;
    }

    //todo body?
    public static function checkResponse(array $response, &$body, array $allowedStatus = [200, 204]): bool
    {
        if ($response['state'] == 'fulfilled') {
            $status = $response['value']->getStatusCode();
            if (in_array($status, $allowedStatus)) {
                $body = $response['value']->getBody()->getContents();
                return true;
            } else {
                ilObjCmiXapi::log()->error("LRS error: " . $response['value']->getBody()->getContents());
                return false;
            }
        } else {
            try {
                ilObjCmiXapi::log()->error("Connection error: " . $response['reason']->getMessage());
            } catch (Exception $e) {
                ilObjCmiXapi::log()->error('error:' . $e->getMessage());
            }
            return false;
        }
    }

    //todo
    public static function buildQuery(array $params, $encoding = PHP_QUERY_RFC3986): string
    {
        if ($params === []) {
            return '';
        }

        if ($encoding === false) {
            $encoder = fn($str) => $str;
        } elseif ($encoding === PHP_QUERY_RFC3986) {
            $encoder = 'rawurlencode';
        } elseif ($encoding === PHP_QUERY_RFC1738) {
            $encoder = 'urlencode';
        } else {
            throw new \InvalidArgumentException('Invalid type');
        }

        $qs = '';
        foreach ($params as $k => $v) {
            $k = $encoder($k);
            if (!is_array($v)) {
                $qs .= $k;
                if ($v !== null) {
                    $qs .= '=' . $encoder($v);
                }
                $qs .= '&';
            } else {
                foreach ($v as $vv) {
                    $qs .= $k;
                    if ($vv !== null) {
                        $qs .= '=' . $encoder($vv);
                    }
                    $qs .= '&';
                }
            }
        }
        return $qs ? (string) substr($qs, 0, -1) : '';
    }
}
