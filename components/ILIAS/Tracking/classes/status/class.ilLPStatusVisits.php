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

use ILIAS\DI\Container;
use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;

class ilLPStatusVisits extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_visits';
    protected const string LNG_TEXT_INFO = 'trac_mode_visits_info';
    protected TrackingDBFactoryInterface $tracking_db_factory;
    protected ilLanguage $lng;

    public static function _getInProgress(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $required_visits = $status_info['visits'];
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['read_count'] < $required_visits) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $required_visits = $status_info['visits'];
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['read_count'] >= $required_visits) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $status_info['visits'] = ilLPObjSettings::_lookupVisits($a_obj_id);
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (
            strcmp($this->ilObjDataCache->lookupType($a_obj_id), 'lm') === 0 &&
            ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)
        ) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
            $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
            $required_visits = $status_info['visits'];
            $re = ilChangeEvent::_lookupReadEvents(
                $a_obj_id,
                $a_usr_id
            );
            if (($re[0]['read_count'] ?? 0) >= $required_visits) {
                $status = self::LP_STATUS_COMPLETED_NUM;
            }
        }
        return $status;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $reqv = ilLPObjSettings::_lookupVisits($a_obj_id);
        $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_usr_id);
        $rc = (int) ($re[0]["read_count"] ?? 0);
        if ($reqv > 0 && $rc) {
            $per = (int) min(100, 100 / $reqv * $rc);
        } else {
            $per = 100;
        }
        return $per;
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
        $this->tracking_db_factory = new TrackingDBFactory($DIC->database());
    }

    public function getCustomLPSettingsExportXML(
        int $object_id
    ): SimpleXMLElement {
        $xml_root = new SimpleXMLElement('<LPStatusVisits></LPStatusVisits>');
        $lp_settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($object_id);
        $visits = is_null($lp_settings)
            ? ilLPObjSettings::LP_DEFAULT_VISITS
            : $lp_settings->getVisits();
        $xml_root->addAttribute('visits', (string) $visits);
        return $xml_root;
    }

    public function importCustomLPSettingsExportXML(
        int $new_object_id,
        ilImportMapping $a_mapping,
        SimpleXMLElement $additional_xml_root
    ): void {
        $settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($new_object_id);
        $settings = $settings->withVisits((int) $additional_xml_root->attributes()->visits);
        $this->tracking_db_factory->lpSettings()->repository()->writeLPSettings($settings);
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_VISITS;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
