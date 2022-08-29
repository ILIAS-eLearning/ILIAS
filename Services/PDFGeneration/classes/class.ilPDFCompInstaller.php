<?php

declare(strict_types=1);

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
    private const PURPOSE_CONF_TABLE = "pdfgen_conf";
    private const PURPOSE_MAP_TABLE = "pdfgen_map";
    private const PURPOSE_PURPOSES_TABLE = "pdfgen_purposes";
    private const RENDERER_TABLE = "pdfgen_renderer";
    private const RENDERER_AVAIL_TABLE = "pdfgen_renderer_avail";

    public static function registerPurpose(string $service, string $purpose, string $preferred): void
    {
        self::addPurpose($service, $purpose);
        self::addPreferred($service, $purpose, $preferred);
    }

    protected static function addPurpose(string $service, string $purpose): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::PURPOSE_PURPOSES_TABLE,
            [
                'purpose_id' => ['int', $ilDB->nextId(self::PURPOSE_PURPOSES_TABLE)],
                'service' => ['text', $service],
                'purpose' => ['text', $purpose],
            ]
        );
    }

    protected static function addPreferred(string $service, string $purpose, string $preferred): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->insert(
            self::PURPOSE_MAP_TABLE,
            [
                'map_id' => ['int', $ilDB->nextId(self::PURPOSE_MAP_TABLE)],
                'service' => ['text', $service],
                'purpose' => ['text', $purpose],
                'preferred' => ['text', $preferred],
                'selected' => ['text', $preferred]
            ]
        );
    }

    public static function unregisterPurpose(string $service, string $purpose): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE .
            " WHERE service = " . $ilDB->quote($service, "text") . " AND purpose = " . $ilDB->quote($purpose, "text"));
    }

    public static function unregisterPreferred(string $service, string $purpose, string $preferred): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_MAP_TABLE .
            " WHERE service = " . $ilDB->quote($service, "text") . " AND purpose = " . $ilDB->quote($purpose, "text") .
            " AND preferred = " . $ilDB->quote($preferred, "text"));
    }

    public static function flushPurposes(string $service): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate("DELETE FROM " . self::PURPOSE_PURPOSES_TABLE . " WHERE service = " . $ilDB->quote(
            $service,
            "text"
        ));
    }

    public static function isPurposeRegistered(string $service, string $purpose): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT count(*) num FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = '
            . $ilDB->quote($service, 'text') . ' AND purpose = ' . $ilDB->quote($purpose, 'text');
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);

        return is_array($row) && (int) $row['num'] !== 0;
    }

    /**
     * @param string $service
     * @return string[]
     */
    public static function getPurposesByService(string $service): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' WHERE service = ' . $ilDB->quote(
            $service,
            'text'
        );
        $result = $ilDB->query($query);
        $purposes = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $purposes[] = $row['purpose'];
        }
        return $purposes;
    }

    /**
     * @return string[]
     */
    public static function getServices(): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT service FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service';
        $result = $ilDB->query($query);
        $services = [];
        while ($row = $ilDB->fetchAssoc($result)) {
            $services[] = $row['service'];
        }
        return $services;
    }

    public static function checkForMultipleServiceAndPurposeCombination(): bool
    {
        global $DIC;
        $ilDB = $DIC->database();
        $query = 'SELECT service, purpose FROM ' . self::PURPOSE_PURPOSES_TABLE . ' GROUP BY service, purpose having count(*) > 1';
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);

        return is_array($row) && !empty($row);
    }

    public static function doCleanUp(): void
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

    public static function updateFromXML(string $service, string $purpose, string $preferred): void
    {
        $parts = explode('/', $service);
        $service = $parts[1];

        if (!self::isPurposeRegistered($service, $purpose)) {
            self::registerPurpose($service, $purpose, $preferred);
        }
    }

    public static function registerRenderer(string $renderer, string $path): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::RENDERER_TABLE,
            [
                'renderer_id' => ['int', $ilDB->nextId(self::RENDERER_TABLE)],
                'renderer' => ['text', $renderer],
                'path' => ['text', $path]
            ]
        );
    }

    public static function registerRendererAvailability(string $renderer, string $service, string $purpose): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->insert(
            self::RENDERER_AVAIL_TABLE,
            [
                'availability_id' => ['int', $ilDB->nextId(self::RENDERER_AVAIL_TABLE)],
                'service' => ['text', $service],
                'purpose' => ['text', $purpose],
                'renderer' => ['text', $renderer]
            ]
        );
    }
}
