<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;
use ILIAS\COPage\Setup\ilCOPageDBUpdateSteps;

class ilComponentDefinitionsStoredObjective implements Setup\Objective
{
    /**
     * @var	bool
     */
    protected $populate_before;

    public function __construct(bool $populate_before = true)
    {
        $this->populate_before = $populate_before;
    }

    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Module- and Servicedefinitions are stored. Events are initialized.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new \ilDatabaseUpdatedObjective(),
            new \ilDatabaseUpdateStepsExecutedObjective(new ilCOPageDBUpdateSteps())
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ilias_path = __DIR__ . "/../../../..";

        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["DIC"]["ilIliasIniFile"] = $ini;
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["ilObjDataCache"] = null;
        $GLOBALS["DIC"]["lng"] = new class() {
            public function loadLanguageModule()
            {
            }
        };
        $GLOBALS["DIC"]["ilLog"] = new class() {
            public function write()
            {
            }
            public function debug()
            {
            }
        };
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() {
            public function getRootLogger() : \ilLogger
            {
                return new class() extends \ilLogger {
                    public function isHandling($a_level)
                    {
                        return false;
                    }

                    public function log($a_message, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function dump($a_variable, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function debug($a_message, $a_context = array())
                    {
                    }

                    public function info($a_message)
                    {
                    }

                    public function notice($a_message)
                    {
                    }

                    public function warning($a_message)
                    {
                    }

                    public function error($a_message)
                    {
                    }

                    public function critical($a_message)
                    {
                    }

                    public function alert($a_message)
                    {
                    }

                    public function emergency($a_message)
                    {
                    }

                    public function getLogger()
                    {
                    }

                    public function write($a_message, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function writeLanguageLog($a_topic, $a_lang_key)
                    {
                    }

                    public function logStack($a_level = null, $a_message = '')
                    {
                    }

                    public function writeMemoryPeakUsage($a_level)
                    {
                    }
                };
            }
            public function getLogger() : \ilLogger
            {
                return new class() extends \ilLogger {
                    public function isHandling($a_level)
                    {
                        return false;
                    }

                    public function log($a_message, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function dump($a_variable, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function debug($a_message, $a_context = array())
                    {
                    }

                    public function info($a_message)
                    {
                    }

                    public function notice($a_message)
                    {
                    }

                    public function warning($a_message)
                    {
                    }

                    public function error($a_message)
                    {
                    }

                    public function critical($a_message)
                    {
                    }

                    public function alert($a_message)
                    {
                    }

                    public function emergency($a_message)
                    {
                    }

                    public function getLogger()
                    {
                    }

                    public function write($a_message, $a_level = ilLogLevel::INFO)
                    {
                    }

                    public function writeLanguageLog($a_topic, $a_lang_key)
                    {
                    }

                    public function logStack($a_level = null, $a_message = '')
                    {
                    }

                    public function writeMemoryPeakUsage($a_level)
                    {
                    }
                };
            }
        };
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }
        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        $reader = new \ilComponentDefinitionReader(
            new \ilGeneralComponentDefinitionProcessor($db),
            new \ilBadgeDefinitionProcessor($db),
            new \ilCOPageDefinitionProcessor($db),
            new \ilComponentInfoDefinitionProcessor($db),
            new \ilCronDefinitionProcessor($db),
            new \ilEventDefinitionProcessor($db),
            new \ilLoggingDefinitionProcessor($db),
            new \ilMailTemplateContextDefinitionProcessor($db),
            new \ilObjectDefinitionProcessor($db),
            new \ilPDFGenerationDefinitionProcessor($db),
            new \ilSystemCheckDefinitionProcessor($db),
            new \ilSecurePathDefinitionProcessor($db),
        );
        $reader->purge();
        $reader->readComponentDefinitions();

        $GLOBALS["DIC"] = $DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
