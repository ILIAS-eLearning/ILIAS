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

/**
 * @author Stefan Meyer <meyer@leifos.de>
 */
class ilADTLocationSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected ?int $radius = null;
    protected bool $force_valid = false;

    protected ilLanguage $lng;

    public function __construct(ilADTDefinition $a_adt_def)
    {
        global $DIC;

        $this->lng = $DIC->language();
        parent::__construct($a_adt_def);
    }

    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTLocationDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            // :TODO:
        }
    }

    // form

    public function addToForm(): void
    {
        $adt = $this->getADT();

        $default = false;
        if ($adt->isNull()) {
            // see ilPersonalProfileGUI::addLocationToForm()

            // use installation default
            $def = ilMapUtil::getDefaultSettings();
            $adt->setLatitude((float) ($def["latitude"] ?? null));
            $adt->setLongitude((float) ($def["longitude"] ?? null));
            $adt->setZoom((int) ($def["zoom"] ?? 0));

            $default = true;
        }

        $optional = new ilCheckboxInputGUI($this->getTitle(), $this->addToElementId("tgl"));

        if (!$default && !$adt->isNull()) {
            $optional->setChecked(true);
        }

        $loc = new ilLocationInputGUI($this->lng->txt("location"), $this->getElementId());
        $loc->setLongitude($adt->getLongitude());
        $loc->setLatitude($adt->getLatitude());
        $loc->setZoom($adt->getZoom());
        $optional->addSubItem($loc);

        $rad = new ilNumberInputGUI($this->lng->txt("form_location_radius"), $this->addToElementId("rad"));
        $rad->setSize(4);
        $rad->setSuffix($this->lng->txt("form_location_radius_km"));
        $rad->setValue($this->radius);
        $rad->setRequired(true);
        $optional->addSubItem($rad);

        $this->addToParentElement($optional);
    }

    protected function shouldBeImportedFromPost($a_post): bool
    {
        return (bool) ($a_post["tgl"] ?? false);
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $tgl = $this->getForm()->getItemByPostVar($this->addToElementId("tgl"));
            $tgl->setChecked(true);

            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setLongitude($post["longitude"]);
            $item->setLatitude($post["latitude"]);
            $item->setZoom($post["zoom"]);

            $this->radius = (int) $post["rad"];

            $this->getADT()->setLongitude($post["longitude"]);
            $this->getADT()->setLatitude($post["latitude"]);
            $this->getADT()->setZoom($post["zoom"]);
        } else {
            // optional empty is valid
            $this->force_valid = true;

            $this->getADT()->setLongitude(null);
            $this->getADT()->setLatitude(null);
            $this->getADT()->setZoom(null);
            $this->radius = null;
        }
        return true;
    }

    public function isValid(): bool
    {
        return (parent::isValid() && ((int) $this->radius || $this->force_valid));
    }


    // bounding

    /**
     * Get bounding box for location circum search
     * @param float $a_latitude
     * @param float $a_longitude
     * @param int   $a_radius
     * @return array
     */
    protected function getBoundingBox(float $a_latitude, float $a_longitude, int $a_radius): array
    {
        $earth_radius = 6371;

        // http://www.d-mueller.de/blog/umkreissuche-latlong-und-der-radius/
        $max_lat = $a_latitude + rad2deg($a_radius / $earth_radius);
        $min_lat = $a_latitude - rad2deg($a_radius / $earth_radius);
        $max_long = $a_longitude + rad2deg($a_radius / $earth_radius / cos(deg2rad($a_latitude)));
        $min_long = $a_longitude - rad2deg($a_radius / $earth_radius / cos(deg2rad($a_latitude)));

        return array(
            "lat" => array("min" => $min_lat, "max" => $max_lat)
            ,
            "long" => array("min" => $min_long, "max" => $max_long)
        );
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        if (!$this->isNull() && $this->isValid()) {
            $box = $this->getBoundingBox(
                $this->getADT()->getLatitude(),
                $this->getADT()->getLongitude(),
                $this->radius
            );

            $res = [];
            $res[] = $a_element_id . "_lat >= " . $this->db->quote($box["lat"]["min"], "float");
            $res[] = $a_element_id . "_lat <= " . $this->db->quote($box["lat"]["max"], "float");
            $res[] = $a_element_id . "_long >= " . $this->db->quote($box["long"]["min"], "float");
            $res[] = $a_element_id . "_long <= " . $this->db->quote($box["long"]["max"], "float");

            return "(" . implode(" AND ", $res) . ")";
        }
        return '';
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array(
                "lat" => $this->getADT()->getLatitude()
                ,
                "long" => $this->getADT()->getLongitude()
                ,
                "zoom" => $this->getADT()->getZoom()
                ,
                "radius" => (int) $this->radius
            ));
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setLatitude($a_value["lat"]);
            $this->getADT()->setLongitude($a_value["long"]);
            $this->getADT()->setZoom($a_value["zoom"]);
            $this->radius = (int) $a_value["radius"];
        }
    }
}
