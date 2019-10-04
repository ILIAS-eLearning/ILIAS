<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitExporter;

/**
 * Class OrgUnitTree
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
class OrgUnitTree extends Base
{

    const ORGU_REF_ID = 'orgu_ref_id';
    const ORG_UNIT_TREE = 'OrgUnitTree';


    /**
     * @param array $params
     *
     * @return mixed|string
     */
    protected function run(array $params)
    {

        $orgu_ref_id = $params[self::ORGU_REF_ID];

        $orgu_exporter = new ilOrgUnitExporter();

        $writer = $orgu_exporter->simpleExport($orgu_ref_id);

        return $writer->xmlFormatData($writer->xmlStr);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getOrgUnitTree";
    }


    /**
     * @return array
     */
    protected function getAdditionalInputParams()
    {
        return array(
            self::ORGU_REF_ID => Base::TYPE_INT,
        );
    }


    /**
     * @inheritdoc
     */
    public function getOutputParams()
    {
        return array(self::ORG_UNIT_TREE => Base::TYPE_STRING);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns the ILIAS Organisational Units";
    }
}
