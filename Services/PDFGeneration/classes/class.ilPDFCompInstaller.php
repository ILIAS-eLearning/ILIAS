<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilPDFCompInstaller
{
    const PURPOSE_CONF_TABLE = "pdfgen_conf";
    const PURPOSE_MAP_TABLE = "pdfgen_map";
    const PURPOSE_PURPOSES_TABLE = "pdfgen_purposes";
    const RENDERER_TABLE = "pdfgen_renderer";
    const RENDERER_AVAIL_TABLE = "pdfgen_renderer_avail";

    public static function registerPurpose(string $service, string $purpose, string $preferred) : void
    {
        self::addPurpose($service, $purpose);
        self::addPreferred($service, $purpose, $preferred);
    }

    protected static function addPurpose(string $service, string $purpose) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::PURPOSE_PURPOSES_TABLE,
            array(
                'purpose_id' => array('int',	$ilDB->nextId(self::PURPOSE_PURPOSES_TABLE)),
                'service' => array('text', 	$service),
                'purpose' => array('text', 	$purpose),
            )
        );
    }

    protected static function addPreferred(string $service, string $purpose, string $preferred) : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->insert(
            self::PURPOSE_MAP_TABLE,
            array(
                'map_id' => array('int', $ilDB->nextId(self::PURPOSE_MAP_TABLE)),
                'service' => array('text', $service),
                'purpose' => array('text', $purpose),
                'preferred' => array('text', $preferred),
                'selected' => array('text', $preferred)
            )
        );
    }

    public static function unregisterPurpose(string $service, string $purpose) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE .
            " WHERE service = " . $ilDB->quote($service, "txt") . " AND purpose = " . $ilDB->quote($purpose, "txt"));
    }

    public static function unregisterPreferred(string $service, string $purpose, string $preferred) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_MAP_TABLE .
            " WHERE service = " . $ilDB->quote($service, "txt") . " AND purpose = " . $ilDB->quote($purpose, "txt") .
            " AND preferred = " . $ilDB->quote($preferred, "txt"));
    }

    public static function flushPurposes(string $service) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE . " WHERE service = " . $ilDB->quote($service, "txt"));
    }

    public static function isPurposeRegistered(string $service, string $purpose) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT count(*) num FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = '
            . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if ($row['num'] != 0) {
            return true;
        }
        return false;
    }

    public static function getPurposesByService(string $service) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = ' . $ilDB->quote($service, 'text');
        $result = $ilDB->query($query);
        $purposes = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $purposes[] = $row['purpose'];
        }
        return $purposes;
    }

    public static function getServices() : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT service FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service';
        $result = $ilDB->query($query);
        $services = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $services[] = $row['service'];
        }
        return $services;
    }

    public static function checkForMultipleServiceAndPurposeCombination() : bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = 'SELECT service, purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service, purpose having count(*) > 1';
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if (is_array($row) && count($row) > 0) {
            return true;
        }
        return false;
    }

    public static function doCleanUp() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = 'SELECT service, purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service, purpose having count(*) > 1';
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            self::unregisterPurpose($row['service'], $row['purpose']);
            self::addPurpose($row['service'], $row['purpose']);
        }

        $query = 'SELECT service, purpose, preferred FROM ' . self::PURPOSE_MAP_TABLE . ' GROUP BY service, purpose, preferred having count(*) > 1';

        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            self::unregisterPreferred($row['service'], $row['purpose'], $row['preferred']);
            self::addPreferred($row['service'], $row['purpose'], $row['preferred']);
        }
    }

    public static function updateFromXML(string $service, string $purpose, string $preferred) : void
    {
        $parts = explode('/', $service);
        $service = $parts[1];

        if (!self::isPurposeRegistered($service, $purpose)) {
            self::registerPurpose($service, $purpose, $preferred);
        }
    }

    public static function registerRenderer(string $renderer, string $path) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::RENDERER_TABLE,
            array(
                      'renderer_id' => array('int',	$ilDB->nextId(self::RENDERER_TABLE)),
                      'renderer' => array('text', 	$renderer),
                      'path' => array('text', 	$path)
                  )
        );
    }

    public static function registerRendererAvailability(string $renderer, string $service, string $purpose) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::RENDERER_AVAIL_TABLE,
            array(
                      'availability_id' => array('int',	$ilDB->nextId(self::RENDERER_AVAIL_TABLE)),
                      'service' => array('text', 	$service),
                      'purpose' => array('text', 	$purpose),
                      'renderer' => array('text', 	$renderer)
                  )
        );
    }
}
