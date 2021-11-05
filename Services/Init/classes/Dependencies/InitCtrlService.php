<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

/**
 * Class InitCtrlService wraps the initialization of ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This class exists because due to dependency-injection the
 * initialization of ilCtrl got a little more complicated -
 * and is used on several occasions.
 */
final class InitCtrlService
{
    /**
     * Initializes the ilCtrl service.
     *
     * This method EXPECTS that $GLOBALS['DIC'] is already initialized
     * with the http services and the refinery factory.
     *
     * @param Container $dic
     * @throws ilCtrlException if the initialization fails.
     */
    public function init(Container $dic) : void
    {
        try {
            $ctrl_structure = new ilCtrlStructure(
                require ilCtrlStructureArtifactObjective::ARTIFACT_PATH,
                require ilCtrlPluginStructureArtifactObjective::ARTIFACT_PATH,
                require ilCtrlBaseClassArtifactObjective::ARTIFACT_PATH,
                require ilCtrlSecurityArtifactObjective::ARTIFACT_PATH
            );
        } catch (Throwable $t) {
            throw new ilCtrlException(self::class . " could not require artifacts, try `composer du` first.");
        }

        // create global instance of ilCtrl
        $GLOBALS['ilCtrl'] = new ilCtrl(
            $ctrl_structure,
            $dic["http.response_sender_strategy"],
            $dic->http()->request(),
            $dic->http()->wrapper()->post(),
            $dic->http()->wrapper()->query(),
            $dic->refinery()
        );

        // add helper function to DI container that
        // returns the global instance.
        $dic['ilCtrl'] = static function () {
            return $GLOBALS['ilCtrl'];
        };
    }
}