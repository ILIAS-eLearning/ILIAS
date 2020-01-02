<?php

class ilPDFCompInstaller
{
    const PURPOSE_CONF_TABLE 		= "pdfgen_conf";
    const PURPOSE_MAP_TABLE 		= "pdfgen_map";
    const PURPOSE_PURPOSES_TABLE 	= "pdfgen_purposes";
    const RENDERER_TABLE			= "pdfgen_renderer";
    const RENDERER_AVAIL_TABLE 		= "pdfgen_renderer_avail";

    /**
     * @param string $service
     * @param string $purpose
     * @param string $preferred
     *
     * @return void
     */
    public static function registerPurpose($service, $purpose, $preferred)
    {
        self::addPurpose($service, $purpose);
        self::addPreferred($service, $purpose, $preferred);
    }

    /**
     * @param $service
     * @param $purpose
     */
    protected static function addPurpose($service, $purpose)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            self::PURPOSE_PURPOSES_TABLE,
            array(
                'purpose_id'	=>	array('int',	$ilDB->nextId(self::PURPOSE_PURPOSES_TABLE)),
                'service' 		=>	array('text', 	$service),
                'purpose' 		=>	array('text', 	$purpose),
            )
        );
    }

    /**
     * @param $service
     * @param $purpose
     * @param $preferred
     */
    protected static function addPreferred($service, $purpose, $preferred)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];
        $ilDB->insert(
            self::PURPOSE_MAP_TABLE,
            array(
                'map_id'    => array('int', $ilDB->nextId(self::PURPOSE_MAP_TABLE)),
                'service'   => array('text', $service),
                'purpose'   => array('text', $purpose),
                'preferred' => array('text', $preferred),
                'selected'  => array('text', $preferred)
            )
        );
    }

    /**
     * @param $service
     * @param $purpose
     */
    public static function unregisterPurpose($service, $purpose)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE .
            " WHERE service = " . $ilDB->quote($service, "txt") . " AND purpose = " . $ilDB->quote($purpose, "txt"));
    }

    /**
     * @param $service
     * @param $purpose
     * @param $preferred
     */
    public static function unregisterPreferred($service, $purpose, $preferred)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_MAP_TABLE .
            " WHERE service = " . $ilDB->quote($service, "txt") . " AND purpose = " . $ilDB->quote($purpose, "txt") .
            " AND preferred = " . $ilDB->quote($preferred, "txt"));
    }

    /**
     * @param string $service
     *
     * @return void
     */
    public static function flushPurposes($service)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE . " WHERE service = " . $ilDB->quote($service, "txt"));
    }

    /**
     * @param string $service
     * @param string $purpose
     *
     * @return boolean
     */
    public static function isPurposeRegistered($service, $purpose)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT count(*) num FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = '
            . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if ($row['num'] != 0) {
            return true;
        }
        return false;
    }

    /**
     * @param string $service
     *
     * @return string[]
     */
    public static function getPurposesByService($service)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = ' . $ilDB->quote($service, 'text');
        $result = $ilDB->query($query);
        $purposes = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $purposes[] = $row['purpose'];
        }
        return $purposes;
    }

    /**
     * @return string[]
     */
    public static function getServices()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = 'SELECT service FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service';
        $result = $ilDB->query($query);
        $services = array();
        while ($row = $ilDB->fetchAssoc($result)) {
            $services[] = $row['service'];
        }
        return $services;
    }

    /**
     * @return bool
     */
    public static function checkForMultipleServiceAndPurposeCombination()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $query = 'SELECT service, purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service, purpose having count(*) > 1';
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if (is_array($row) && count($row) > 0) {
            return true;
        }
        return false;
    }

    public static function doCleanUp()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
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

    /**
     * @param $service
     * @param $purpose
     * @param $preferred
     */
    public static function updateFromXML($service, $purpose, $preferred)
    {
        $parts = explode('/', $service);
        $service = $parts[1];

        if (!self::isPurposeRegistered($service, $purpose)) {
            self::registerPurpose($service, $purpose, $preferred);
        }
    }

    /**
     * @param $renderer
     * @param $path
     */
    public static function registerRenderer($renderer, $path)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            self::RENDERER_TABLE,
            array(
                      'renderer_id'	=>	array('int',	$ilDB->nextId(self::RENDERER_TABLE)),
                      'renderer' 	=>	array('text', 	$renderer),
                      'path' 		=>	array('text', 	$path)
                  )
        );
    }

    /**
     * @param $renderer
     * @param $service
     * @param $purpose
     */
    public static function registerRendererAvailability($renderer, $service, $purpose)
    {
        global $DIC;
        /** @var ilDB $ilDB */
        $ilDB = $DIC['ilDB'];

        $ilDB->insert(
            self::RENDERER_AVAIL_TABLE,
            array(
                      'availability_id'	=>	array('int',	$ilDB->nextId(self::RENDERER_AVAIL_TABLE)),
                      'service' 		=>	array('text', 	$service),
                      'purpose' 		=>	array('text', 	$purpose),
                      'renderer' 		=>	array('text', 	$renderer)
                  )
        );
    }
}
