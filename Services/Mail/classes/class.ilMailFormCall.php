<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Statically used helper class for generating links to the mail form user interface
 *
 * @version: $Id$
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailFormCall
{
    /**
     *
     */
    const SESSION_KEY = 'mail_transport';

    /**
     * HTTP-GET parameter for the referer url
     */
    const REFERER_KEY = 'r';

    /**
     * Session parameter for the hash
     */
    const SIGNATURE_KEY = 'sig';

    /**
     * Session parameter for the context
     */
    const CONTEXT_PREFIX = 'ctx';

    /**
     * Session parameter for the context
     */
    const CONTEXT_KEY = 'ctx_template';

    /**
     * @param object|string $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @param array $context_params
     * @return string
     */
    public static function getLinkTarget(
        $gui,
        string $cmd,
        array $gui_params = [],
        array $mail_params = [],
        $context_params = []
    ) : string {
        return self::getTargetUrl('&', $gui, $cmd, $gui_params, $mail_params, $context_params);
    }

    /**
     * @param object|string $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @param array $context_params
     * @return string
     */
    public static function getRedirectTarget(
        $gui,
        string $cmd,
        array $gui_params = [],
        array $mail_params = [],
        array $context_params = []
    ) : string {
        return self::getTargetUrl('&', $gui, $cmd, $gui_params, $mail_params, $context_params);
    }

    /**
     * @param string $argument_separator
     * @param object|string $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @param array $context_params
     * @return string
     */
    protected static function getTargetUrl(
        string $argument_separator,
        $gui,
        string $cmd,
        array $gui_params = [],
        array $mail_params = [],
        array $context_params = []
    ) : string {
        global $DIC;

        $mparams = '';
        $referer = '';

        foreach ($mail_params as $key => $value) {
            $mparams .= $argument_separator . $key . '=' . urlencode($value);
        }

        foreach ($context_params as $key => $value) {
            if ($key == self::CONTEXT_KEY) {
                $mparams .= $argument_separator . $key . '=' . urlencode($value);
            } else {
                $mparams .= $argument_separator . self::CONTEXT_PREFIX . '_' . $key . '=' . urlencode($value);
            }
        }

        if (is_object($gui)) {
            $ilCtrlTmp = clone $DIC->ctrl();
            foreach ($gui_params as $key => $value) {
                $ilCtrlTmp->setParameter($gui, $key, $value);
            }
            $referer = $ilCtrlTmp->getLinkTarget($gui, $cmd, '', false, false);
        } elseif (is_string($gui)) {
            $referer = $gui;
        }

        $referer = $argument_separator . self::REFERER_KEY . '=' . rawurlencode(base64_encode($referer));

        return 'ilias.php?baseClass=ilMailGUI' . $referer . $mparams;
    }

    /**
     * @param array<string, string> $queryParameters
     */
    public static function storeReferer(array $queryParameters) : void
    {
        $session = ilSession::get(self::SESSION_KEY);

        if (isset($queryParameters[self::REFERER_KEY])) {
            $session[self::REFERER_KEY] = base64_decode(rawurldecode($queryParameters[self::REFERER_KEY]));
            $session[self::SIGNATURE_KEY] = base64_decode(rawurldecode($queryParameters[self::SIGNATURE_KEY] ?? ''));

            $contextParameters = [];
            foreach ($queryParameters as $key => $value) {
                $prefix = substr($key, 0, strlen(self::CONTEXT_PREFIX));
                if ($prefix == self::CONTEXT_PREFIX) {
                    if ($key == self::CONTEXT_KEY) {
                        $contextParameters[$key] = $value;
                    } else {
                        $contextParameters[substr($key, strlen(self::CONTEXT_PREFIX . '_'))] = $value;
                    }
                }
            }
            $session[self::CONTEXT_PREFIX] = $contextParameters;
        } else {
            if (isset($session[self::REFERER_KEY])) {
                unset($session[self::REFERER_KEY]);
            }
            if (isset($session[self::SIGNATURE_KEY])) {
                unset($session[self::SIGNATURE_KEY]);
            }
            if (isset($session[self::CONTEXT_PREFIX])) {
                unset($session[self::CONTEXT_PREFIX]);
            }
        }

        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @return string signature
     */
    public static function getSignature() : string
    {
        $sig = '';
        $session = ilSession::get(self::SESSION_KEY);

        if (isset($session[self::SIGNATURE_KEY])) {
            $sig = $session[self::SIGNATURE_KEY];

            unset($session[self::SIGNATURE_KEY]);
            ilSession::set(self::SESSION_KEY, $session);
        }

        return $sig;
    }

    /**
     * @return string
     */
    public static function getRefererRedirectUrl() : string
    {
        $url = '';
        $session = ilSession::get(self::SESSION_KEY);

        if (isset($session[self::REFERER_KEY])) {
            $url = $session[self::REFERER_KEY];
            if (is_string($url) && $url !== '') {
                $parts = parse_url($url);
                if (isset($parts['query']) && $parts['query'] !== '') {
                    $url .= '&returned_from_mail=1';
                } else {
                    $url .= '?returned_from_mail=1';
                }

                $ilias_url_parts = parse_url(ilUtil::_getHttpPath());
                if (isset($parts['host']) && $ilias_url_parts['host'] !== $parts['host']) {
                    $url = 'ilias.php?baseClass=ilMailGUI';
                }
            }

            unset($session[self::REFERER_KEY]);
            ilSession::set(self::SESSION_KEY, $session);
        }

        return $url;
    }

    /**
     * @return bool
     */
    public static function isRefererStored() : bool
    {
        $session = ilSession::get(self::SESSION_KEY);

        return (
            isset($session[self::REFERER_KEY]) &&
            is_string($session[self::REFERER_KEY]) &&
            $session[self::REFERER_KEY] !== ''
        );
    }

    /**
     * @return string|null
     */
    public static function getContextId() : ?string
    {
        $session = ilSession::get(self::SESSION_KEY);
        return (
            isset($session[self::CONTEXT_PREFIX][self::CONTEXT_KEY]) &&
            is_string($session[self::CONTEXT_PREFIX][self::CONTEXT_KEY]) ?
            $session[self::CONTEXT_PREFIX][self::CONTEXT_KEY] : null
        );
    }

    /**
     * @param string|null $id
     */
    public static function setContextId(?string $id) : void
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session[self::CONTEXT_KEY] = $id;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @return array context parameters
     */
    public static function getContextParameters() : array
    {
        $session = ilSession::get(self::SESSION_KEY);
        if (isset($session[self::CONTEXT_PREFIX]) && is_array($session[self::CONTEXT_PREFIX])) {
            return $session[self::CONTEXT_PREFIX];
        }

        return [];
    }

    /**
     * @param array $parameters
     * @return array
     */
    public static function setContextParameters(array $parameters) : void
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session[self::CONTEXT_PREFIX] = $parameters;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @param string[] $recipients
     */
    public static function setRecipients(array $recipients) : void
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session['rcp_to'] = $recipients;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @return string[]
     */
    public static function getRecipients() : array
    {
        $session = ilSession::get(self::SESSION_KEY);
        if (isset($session['rcp_to']) && is_array($session['rcp_to'])) {
            return $session['rcp_to'];
        }

        return [];
    }
}
