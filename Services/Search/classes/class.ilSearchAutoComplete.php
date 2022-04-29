<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Search Auto Completion Application Class
*/
class ilSearchAutoComplete
{
    public static function getLuceneList(string $a_str) : string
    {
        $qp = new ilLuceneQueryParser('title:' . $a_str . '*');
        $qp->parse();

        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->setType(ilLuceneSearcher::TYPE_STANDARD);
        $searcher->search();
        
        $res = $searcher->getResult()->getCandidates();
        
        $max_entries = ilSearchSettings::getInstance()->getAutoCompleteLength() ?
            ilSearchSettings::getInstance()->getAutoCompleteLength() :
            10;
        
        
        $list = array();
        $num_entries = 0;
        foreach ($res as $res_obj_id) {
            if (self::checkObjectPermission($res_obj_id)) {
                $list[] = ilObject::_lookupTitle($res_obj_id);
                $num_entries++;
            }
            if ($num_entries >= $max_entries) {
                break;
            }
        }
        
        $i = 0;
        $result = array();
        foreach ($list as $entry) {
            $result[$i] = new stdClass();
            $result[$i]->value = '"' . $entry . '"';
            $i++;
        }

        return json_encode($result, JSON_THROW_ON_ERROR);
    }
    
    

    public static function getList(string $a_str) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (ilSearchSettings::getInstance()->enabledLucene()) {
            return self::getLuceneList($a_str);
        }
        
        
        $a_str = str_replace('"', "", $a_str);
        
        $settings = new ilSearchSettings();
        
        $object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','htlm','exc','file','qpl','tst','svy','spl',
            'chat', 'webr','mcst','sess','pg','st','gdf','wiki', 'copa');

        $set = $ilDB->query("SELECT title, obj_id FROM object_data WHERE "
            . $ilDB->like('title', 'text', $a_str . "%") . " AND "
            . $ilDB->in('type', $object_types, false, 'text') . " ORDER BY title");
        $max = ($settings->getAutoCompleteLength() > 0)
            ? $settings->getAutoCompleteLength()
            : 10;
        
        $cnt = 0;
        $list = array();
        $checked = array();
        $lim = "";
        while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max) {
            if (strpos($rec["title"], " ") > 0 || strpos($rec["title"], "-") > 0) {
                $rec["title"] = '"' . $rec["title"] . '"';
            }
            if (!in_array($rec["title"], $list) && !in_array($rec["obj_id"], $checked)) {
                if (ilSearchAutoComplete::checkObjectPermission($rec["obj_id"])) {
                    $list[] = $lim . $rec["title"];
                    $cnt++;
                }
                $checked[] = $rec["obj_id"];
            }
        }
        
        $set = $ilDB->query("SELECT rbac_id,obj_id,obj_type, keyword FROM il_meta_keyword WHERE "
            . $ilDB->like('keyword', 'text', $a_str . "%") . " AND "
            . $ilDB->in('obj_type', $object_types, false, 'text') . " ORDER BY keyword");
        while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max) {
            if (strpos($rec["keyword"], " ") > 0) {
                $rec["keyword"] = '"' . $rec["keyword"] . '"';
            }
            if (!in_array($rec["keyword"], $list) && !in_array($rec["rbac_id"], $checked)) {
                if (ilSearchAutoComplete::checkObjectPermission($rec["rbac_id"])) {
                    $list[] = $lim . $rec["keyword"];
                    $cnt++;
                }
            }
            $checked[] = $rec["rbac_id"];
        }

        $i = 0;
        $result = array();
        foreach ($list as $l) {
            $result[$i] = new stdClass();
            $result[$i]->value = $l;
            $i++;
        }

        return json_encode($result, JSON_THROW_ON_ERROR);
    }

    public static function checkObjectPermission(int $a_obj_id) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $refs = ilObject::_getAllReferences($a_obj_id);
        foreach ($refs as $ref) {
            if ($ilAccess->checkAccess("read", "", $ref)) {
                return true;
            }
        }
        return false;
    }
}
