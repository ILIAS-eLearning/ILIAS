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

/**
 * Service context (factory) class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilContext
{
    protected static string $class_name = "";
    protected static string $type = "";
    
    public const CONTEXT_WEB = ilContextWeb::class;
    public const CONTEXT_CRON = ilContextCron::class;
    public const CONTEXT_RSS = ilContextRss::class;
    public const CONTEXT_ICAL = ilContextIcal::class;
    public const CONTEXT_SOAP = ilContextSoap::class;
    public const CONTEXT_SOAP_NO_AUTH = ilContextSoapNoAuth::class;
    public const CONTEXT_WEBDAV = ilContextWebdav::class;
    public const CONTEXT_RSS_AUTH = ilContextRssAuth::class;
    public const CONTEXT_SESSION_REMINDER = ilContextSessionReminder::class;
    public const CONTEXT_SOAP_WITHOUT_CLIENT = ilContextSoapWithoutClient::class;
    public const CONTEXT_UNITTEST = ilContextUnitTest::class;
    public const CONTEXT_REST = ilContextRest::class;
    public const CONTEXT_SCORM = ilContextScorm::class;
    public const CONTEXT_WAC = ilContextWAC::class;
    public const CONTEXT_APACHE_SSO = ilContextApacheSSO::class;
    public const CONTEXT_SHIBBOLETH = ilContextShibboleth::class;
    public const CONTEXT_LTI_PROVIDER = ilContextLTIProvider::class;
    public const CONTEXT_SAML = ilContextSaml::class;

    /**
     * Init context by type
     */
    public static function init(string $a_type) : bool
    {
        self::$class_name = $a_type;
        self::$type = $a_type;
        
        return true;
    }
    
    /**
     * Call context method directly without internal handling
     * @return mixed
     */
    public static function directCall(string $a_type, string $a_method)
    {
        $class_name = $a_type;
        if ($class_name && method_exists($class_name, $a_method)) {
            return call_user_func(array($class_name, $a_method));
        }

        return null;
    }

    /**
     * Call current content
     * @return mixed
     */
    protected static function callContext(string $a_method, array $args = [])
    {
        if (!self::$class_name) {
            self::init(self::CONTEXT_WEB);
        }
        return call_user_func_array([self::$class_name, $a_method], $args);
    }
    
    /**
     * Are redirects supported?
     */
    public static function supportsRedirects() : bool
    {
        global $DIC;

        $ilCtrl = null;
        if (isset($DIC["ilCtrl"])) {
            $ilCtrl = $DIC->ctrl();
        }
        
        // asynchronous calls must never be redirected
        if ($ilCtrl && $ilCtrl->isAsynch()) {
            return false;
        }
        
        return (bool) self::callContext("supportsRedirects");
    }
    
    /**
     * Based on user authentication?
     */
    public static function hasUser() : bool
    {
        return (bool) self::callContext("hasUser");
    }
    
    /**
     * Uses HTTP aka browser
     */
    public static function usesHTTP() : bool
    {
        return (bool) self::callContext("usesHTTP");
    }
    
    /**
     * Has HTML output
     */
    public static function hasHTML() : bool
    {
        return (bool) self::callContext("hasHTML");
    }
    
    /**
     * Uses template engine
     */
    public static function usesTemplate() : bool
    {
        return (bool) self::callContext("usesTemplate");
    }
    
    /**
     * Init client
     */
    public static function initClient() : bool
    {
        return (bool) self::callContext("initClient");
    }
    
    /**
     * Try authentication
     */
    public static function doAuthentication() : bool
    {
        return (bool) self::callContext("doAuthentication");
    }
    
    /**
     * Supports push messages
     */
    public static function supportsPushMessages() : bool
    {
        return (bool) self::callContext("supportsPushMessages");
    }
    
    /**
     * Get context type
     */
    public static function getType() : string
    {
        return self::$type;
    }
    
    /**
     * Check if context supports persistent
     * session handling.
     * false for cli context
     */
    public static function supportsPersistentSessions() : bool
    {
        return (bool) self::callContext('supportsPersistentSessions');
    }

    /**
     * Context that are not only temporary in a session (e.g. WAC is, Cron is not)
     */
    public static function isSessionMainContext() : bool
    {
        return (bool) self::callContext('isSessionMainContext');
    }

    public static function modifyHttpPath(string $httpPath) : string
    {
        return (string) self::callContext('modifyHttpPath', [$httpPath]);
    }
}
