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
     * @static
     * @param mixed $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @param array $context_params
     * @return string
     */
    public static function getLinkTarget($gui, $cmd, array $gui_params = array(), array $mail_params = array(), $context_params = array())
    {
        return self::getTargetUrl('&amp;', $gui, $cmd, $gui_params, $mail_params, $context_params);
    }

    /**
     * @static
     * @param mixed $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @param array $context_params
     * @return string
     */
    public static function getRedirectTarget($gui, $cmd, array $gui_params = array(), array $mail_params = array(), array $context_params = array())
    {
        return self::getTargetUrl('&', $gui, $cmd, $gui_params, $mail_params, $context_params);
    }

    /**
     * @static
     * @param string $argument_separator
     * @param mixed $gui
     * @param string $cmd
     * @param array $gui_params
     * @param array $mail_params
     * @return string
     */
    protected static function getTargetUrl($argument_separator, $gui, $cmd, array $gui_params = array(), array $mail_params = array(), array $context_params = array())
    {
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
     * @static
     * @param array $request_params
     */
    public static function storeReferer($request_params)
    {
        $session = ilSession::get(self::SESSION_KEY);
        if (isset($request_params[self::REFERER_KEY])) {
            $session[self::REFERER_KEY] = base64_decode(rawurldecode($request_params[self::REFERER_KEY]));
            $session[self::SIGNATURE_KEY] = base64_decode(rawurldecode($request_params[self::SIGNATURE_KEY]));

            $ctx_params = array();
            foreach ($request_params as $key => $value) {
                $prefix = substr($key, 0, strlen(self::CONTEXT_PREFIX));
                if ($prefix == self::CONTEXT_PREFIX) {
                    if ($key == self::CONTEXT_KEY) {
                        $ctx_params[$key] = $value;
                    } else {
                        $ctx_params[substr($key, strlen(self::CONTEXT_PREFIX . '_'))] = $value;
                    }
                }
            }
            $session[self::CONTEXT_PREFIX] = $ctx_params;
        } else {
            unset($session[self::REFERER_KEY]);
            unset($session[self::SIGNATURE_KEY]);
            unset($session[self::CONTEXT_PREFIX]);
        }
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * Get preset signature
     *
     * @return string signature
     */
    public static function getSignature()
    {
        $session = ilSession::get(self::SESSION_KEY);

        $sig = $session[self::SIGNATURE_KEY];

        unset($session[self::SIGNATURE_KEY]);
        ilSession::set(self::SESSION_KEY, $session);

        return $sig;
    }

    /**
     * @static
     * @return string
     */
    public static function getRefererRedirectUrl()
    {
        $session = ilSession::get(self::SESSION_KEY);

        $url = $session[self::REFERER_KEY];
        if (strlen($url)) {
            $parts = parse_url($url);
            if (isset($parts['query']) && strlen($parts['query'])) {
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

        return $url;
    }

    /**
     * @static
     * @return bool
     */
    public static function isRefererStored()
    {
        $session = ilSession::get(self::SESSION_KEY);
        return isset($session[self::REFERER_KEY]) && strlen($session[self::REFERER_KEY]) ? true : false;
    }

    /**
     * @return string|null
     */
    public static function getContextId()
    {
        $session = ilSession::get(self::SESSION_KEY);
        return (
            isset($session[self::CONTEXT_PREFIX][self::CONTEXT_KEY]) &&
            strlen($session[self::CONTEXT_PREFIX][self::CONTEXT_KEY]) ?
            $session[self::CONTEXT_PREFIX][self::CONTEXT_KEY] : null
        );
    }

    /**
     * @param $id string
     */
    public static function setContextId($id)
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session[self::CONTEXT_KEY] = $id;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @return array context parameters
     */
    public static function getContextParameters()
    {
        $session = ilSession::get(self::SESSION_KEY);
        if (isset($session[self::CONTEXT_PREFIX])) {
            return (array) $session[self::CONTEXT_PREFIX];
        }
        return array();
    }

    /**
     * @param array $parameters
     * @return array
     */
    public static function setContextParameters(array $parameters)
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session[self::CONTEXT_PREFIX] = $parameters;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @param array $recipients
     */
    public static function setRecipients(array $recipients)
    {
        $session = ilSession::get(self::SESSION_KEY);
        $session['rcp_to'] = $recipients;
        ilSession::set(self::SESSION_KEY, $session);
    }

    /**
     * @return array
     */
    public static function getRecipients()
    {
        $session = ilSession::get(self::SESSION_KEY);
        return (array) $session['rcp_to'];
    }
}
