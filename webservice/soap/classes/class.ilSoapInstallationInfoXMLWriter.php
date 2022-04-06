<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

class ilSoapInstallationInfoXMLWriter extends ilXmlWriter
{
    protected array $settings = [];

    public function setSettings(array $settings) : void
    {
        $this->settings = $settings;
    }

    public function start() : void
    {
        $this->buildHeader();
        $this->buildInstallationInfo();
        $this->xmlStartTag("Clients");
    }

    public function addClient(?ilSetting $client) : void
    {
        if ($client instanceof ilSetting) {
            $this->buildClient($client);
        }
    }

    public function end() : void
    {
        $this->xmlEndTag("Clients");
        $this->buildFooter();
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }

    private function buildHeader() : void
    {
        // we have to build the http path here since this request is client independent!
        $httpPath = ilSoapFunctions::buildHTTPPath();
        $this->xmlSetDtdDef("<!DOCTYPE Installation PUBLIC \"-//ILIAS//DTD InstallationInfo//EN\" \"" . $httpPath . "/xml/ilias_installation_info_5_3.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS clients.");
        $this->xmlHeader();
        $this->xmlStartTag(
            "Installation",
            array(
                "version" => ILIAS_VERSION,
                "path" => $httpPath,
            )
        );
    }

    private function buildFooter() : void
    {
        $this->xmlEndTag('Installation');
    }

    private function buildClient(ilSetting $setting) : void
    {
        // determine skins/styles
        $skin_styles = array();// TODO PHP8-REVIEW Unnecessary operations
        include_once("./Services/Style/System/classes/class.ilStyleDefinition.php");
        $skins = ilStyleDefinition::getAllSkins();// TODO PHP8-REVIEW Unnecessary operations

        if (is_array($skins)) {// TODO PHP8-REVIEW Unnecessary operations
            foreach ($skins as $skin) {
                foreach ($skin->getStyles() as $style) {
                    include_once("./Services/Style/System/classes/class.ilSystemStyleSettings.php");
                    if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId())) {
                        continue;
                    }
                    $skin_styles [] = $skin->getId() . ":" . $style->getId();
                }
            }
        }

        // timezones
        include_once('Services/Calendar/classes/class.ilTimeZone.php');// TODO PHP8-REVIEW Unnecessary operations

        $this->xmlStartTag(
            "Client",
            array(
                "inst_id" => $setting->get("inst_id"),
                "id" => $setting->clientid,// TODO PHP8-REVIEW Property dynamically declared
                "enabled" => $setting->access == 1 ? "TRUE" : "FALSE",// TODO PHP8-REVIEW Property dynamically declared
                "default_lang" => $setting->language,// TODO PHP8-REVIEW Property dynamically declared

            )
        );
        $this->xmlEndTag("Client");
    }

    private function buildInstallationInfo() : void
    {
        $this->xmlStartTag("Settings");
        $this->xmlElement(
            "Setting",
            array("key" => "default_client"),
            $GLOBALS['DIC']['ilIliasIniFile']->readVariable("clients", "default")
        );
        $this->xmlEndTag("Settings");
    }
}
