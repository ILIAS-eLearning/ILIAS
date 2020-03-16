<?php

declare(strict_types=1);

use ILIAS\Data;
use Pimple\Container;

class ilIndividualAssessmentDIC
{
    public static $dic;

    public static function dic() : Container
    {
        if (!self::$dic) {
            self::$dic = self::buildDIC();
        }

        return self::$dic;
    }

    protected static function buildDIC() : Container
    {
        global $DIC;
        $dic = new Container();

        $dic['DataFactory'] = function () {
            return new Data\Factory();
        };

        $dic['ilIndividualAssessmentPrimitiveInternalNotificator'] = function () {
            return new ilIndividualAssessmentPrimitiveInternalNotificator();
        };

        $dic['ilIndividualAssessmentMemberGUI'] = function ($dic) use ($DIC) {
            return new ilIndividualAssessmentMemberGUI(
                $DIC['ilCtrl'],
                $DIC['lng'],
                $DIC['tpl'],
                $DIC['ilUser'],
                $DIC->ui()->factory()->input(),
                $DIC->ui()->factory()->messageBox(),
                $DIC->ui()->factory()->button(),
                $DIC->refinery(),
                $dic['DataFactory'],
                $DIC->ui()->renderer(),
                $DIC->http()->request(),
                $dic['ilIndividualAssessmentPrimitiveInternalNotificator']
            );
        };

        return $dic;
    }
}
