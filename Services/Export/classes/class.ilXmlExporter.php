<?php declare(strict_types=1);

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

/**
 * Xml Exporter class
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
abstract class ilXmlExporter
{
    protected string $dir_relative = "";
    protected string $dir_absolute = "";
    protected ilExport $exp;

    public function __construct()
    {
    }

    public function setExport(ilExport $a_exp) : void
    {
        $this->exp = $a_exp;
    }

    public function getExport() : ilExport
    {
        return $this->exp;
    }

    public static function lookupExportDirectory(
        string $a_obj_type,
        int $a_obj_id,
        string $a_export_type = 'xml',
        string $a_entity = ""
    ) : string {
        $ent = ($a_entity == "")
            ? ""
            : "_" . $a_entity;

        if ($a_export_type == 'xml') {
            return ilFileUtils::getDataDir() . "/" . $a_obj_type . $ent . "_data" . "/" . $a_obj_type . "_" . $a_obj_id . "/export";
        }
        return ilFileUtils::getDataDir() . "/" . $a_obj_type . $ent . "_data" . "/" . $a_obj_type . "_" . $a_obj_id . "/export_" . $a_export_type;
    }

    abstract public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string;

    abstract public function init() : void;

    public function setExportDirectories(string $a_dir_relative, string $a_dir_absolute) : void
    {
        $this->dir_relative = $a_dir_relative;
        $this->dir_absolute = $a_dir_absolute;
    }

    public function getRelativeExportDirectory() : string
    {
        return $this->dir_relative;
    }

    public function getAbsoluteExportDirectory() : string
    {
        return $this->dir_absolute;
    }

    /**
     * Get head dependencies
     * @return array array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        return [];
    }

    /**
     * Get tail dependencies
     * @return array array of array with keys "component", entity", "ids"
     */
    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        return array();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top. Example:
     *        return array (
     *        "4.1.0" => array(
     *            "namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
     *            "xsd_file" => "ilias_md_4_1.xsd",
     *            "min" => "4.1.0",
     *            "max" => "")
     *        );
     */
    abstract public function getValidSchemaVersions(string $a_entity) : array;

    final public function determineSchemaVersion(
        string $a_entity,
        string $a_target_release
    ) : array {
        $svs = $this->getValidSchemaVersions($a_entity);
        $found = false;
        $rsv = [];
        foreach ($svs as $k => $sv) {
            if (!$found) {
                if (version_compare($sv["min"], ILIAS_VERSION_NUMERIC, "<=")
                    && ($sv["max"] == "" || version_compare($sv["max"], ILIAS_VERSION_NUMERIC, ">="))) {
                    $rsv = $sv;
                    $rsv["schema_version"] = $k;
                    $found = true;
                }
            }
        }
        return $rsv;
    }
}
