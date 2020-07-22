<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Captcha util
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup
 */
class ilCaptchaUtil
{
    const CONTEXT_FORUM = 'frm';
    const CONTEXT_LOGIN = 'auth';
    const CONTEXT_REGISTRATION = 'reg';
    const CONTEXT_WIKI = 'wiki';

    /**
     * @var array|null
     */
    protected static $context_map;

    /**
     * Check whether captcha support is active
     * @return bool
     */
    public static function checkFreetype()
    {
        if (function_exists('imageftbbox')) {
            ilLoggerFactory::getLogger('auth')->debug('Function imageftbox is available.');
            return true;
        }
        ilLoggerFactory::getLogger('auth')->debug('Function imageftbox is not available.');
        return false;
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return bool
     * @throws BadMethodCallException
     */
    public static function __callStatic($name, array $arguments = array())
    {
        if (
            strpos($name, 'isActiveFor') === 0 ||
            strpos($name, 'setActiveFor') === 0
        ) {
            $settings = new ilSetting('cptch');
            $supported_contexts = self::getSupportedContexts();
            $method_parts = explode('_', strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name)));
            $requested_context = strtolower($method_parts[count($method_parts) - 1]);
            if (!isset($requested_context, self::$context_map)) {
                throw new BadMethodCallException('Method ' . $name . ' called for a non existing context.');
            }

            if ('set' == $method_parts[0]) {
                $settings->set('activate_captcha_anonym_' . $supported_contexts[$requested_context], (int) $arguments[0]);
                return;
            } else {
                return self::checkFreetype() && (bool) $settings->get('activate_captcha_anonym_' . $supported_contexts[$requested_context], false);
            }
        } else {
            throw new BadMethodCallException('Call to an undefined static method ' . $name . ' in class ' . __CLASS__ . '.');
        }
    }

    /**
     * @return string
     */
    public static function getPreconditionsMessage()
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule('cptch');
        return "<a target='_blank' href='http://php.net/manual/en/image.installation.php'>" . $lng->txt('cptch_freetype_support_needed') . "</a>";
    }

    /**
     * @return array
     */
    private static function getSupportedContexts()
    {
        if (null !== self::$context_map) {
            return self::$context_map;
        }

        self::$context_map = array();

        $r = new ReflectionClass(new self());
        $constants = $r->getConstants();
        foreach ($constants as $name => $value) {
            if (strpos($name, 'CONTEXT_') === 0) {
                self::$context_map[strtolower(substr($name, strlen('CONTEXT_')))] = $value;
            }
        }

        return self::$context_map;
    }
}
