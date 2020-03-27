<?php

declare(strict_types=1);

use ILIAS\Data;
use Pimple\Container;

trait ilIndividualAssessmentDIC
{
    public function getObjectDIC(
        ilObjIndividualAssessment $object,
        ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container['DataFactory'] = function () {
            return new Data\Factory();
        };

        $container['ilIndividualAssessmentPrimitiveInternalNotificator'] = function () {
            return new ilIndividualAssessmentPrimitiveInternalNotificator();
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
                $c['ilIndividualAssessmentMemberGUI']
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
                $dic['ilErr']
            );
        };

        return $container;
    }
}