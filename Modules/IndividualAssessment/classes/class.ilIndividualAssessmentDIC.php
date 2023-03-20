<?php

declare(strict_types=1);

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

use ILIAS\Data;
use Pimple\Container;

trait ilIndividualAssessmentDIC
{
    public function getObjectDIC(
        ilObjIndividualAssessment $object,
        ArrayAccess $dic
    ): Container {
        $container = new Container();

        $container['DataFactory'] = function () {
            return new Data\Factory();
        };

        $container['ilIndividualAssessmentPrimitiveInternalNotificator'] = function () {
            return new ilIndividualAssessmentPrimitiveInternalNotificator();
        };

        $container['ilIndividualAssessmentSettingsGUI'] = function ($c) use ($object, $dic) {
            return new ilIndividualAssessmentSettingsGUI(
                $object,
                $dic['ilCtrl'],
                $dic['tpl'],
                $dic['lng'],
                $dic['ilTabs'],
                $dic['ui.factory']->input(),
                $dic['refinery'],
                $dic['ui.renderer'],
                $dic['http']->request(),
                $dic['ilErr'],
                $c['ilIndividualAssessmentCommonSettingsGUI']
            );
        };

        $container['ilIndividualAssessmentMembersGUI'] = function ($c) use ($object, $dic) {
            return new ilIndividualAssessmentMembersGUI(
                $object,
                $dic['ilCtrl'],
                $dic['tpl'],
                $dic['lng'],
                $dic["ilToolbar"],
                $dic['ilUser'],
                $dic['ilTabs'],
                $object->accessHandler(),
                $dic['ui.factory'],
                $dic['ui.renderer'],
                $dic['ilErr'],
                $c['ilIndividualAssessmentMemberGUI'],
                $dic->refinery(),
                $dic->http()->wrapper(),
                $c['helper.dateformat']
            );
        };

        $container['ilIndividualAssessmentMemberGUI'] = function ($c) use ($object, $dic) {
            return new ilIndividualAssessmentMemberGUI(
                $dic['ilCtrl'],
                $dic['lng'],
                $dic['tpl'],
                $dic['ilUser'],
                $dic['ui.factory']->input(),
                $dic['ui.factory']->messageBox(),
                $dic['ui.factory']->button(),
                $dic['refinery'],
                $c['DataFactory'],
                $dic['ui.renderer'],
                $dic['http']->request(),
                $c['ilIndividualAssessmentPrimitiveInternalNotificator'],
                $dic["ilToolbar"],
                $object,
                $dic['ilErr'],
                $dic->refinery(),
                $dic->http()->wrapper()->query(),
                $c['helper.dateformat']
            );
        };

        $container['ilIndividualAssessmentCommonSettingsGUI'] = function ($c) use ($object, $dic) {
            return new ilIndividualAssessmentCommonSettingsGUI(
                $object,
                $dic['ilCtrl'],
                $dic['tpl'],
                $dic['lng'],
                $dic->object()
            );
        };

        $container['helper.dateformat'] = function ($c) use ($dic) {
            return new ilIndividualAssessmentDateFormatter(
                $c['DataFactory']
            );
        };

        return $container;
    }
}
