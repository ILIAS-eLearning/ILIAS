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

    public function addClient(string $client_directory) : bool
    {
        return $this->buildClient($client_directory);
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

    private function buildClient(string $client_directory) : bool
    {
        global $DIC;

        $ini_file = "./" . $client_directory . "/client.ini.php";

        // get settings from ini file
        require_once("./Services/Init/classes/class.ilIniFile.php");

        $ilClientIniFile = new ilIniFile($ini_file);
        $ilClientIniFile->read();
        if ($ilClientIniFile->ERROR !== "") {
            return false;
        }
        $client_id = $ilClientIniFile->readVariable('client', 'name');
        if ($ilClientIniFile->variableExists('client', 'expose')) {
            $client_expose = $ilClientIniFile->readVariable('client', 'expose');
            if ($client_expose === "0") {
                return false;
            }
        }

        // build dsn of database connection and connect
        $ilDB = ilDBWrapperFactory::getWrapper(
            $ilClientIniFile->readVariable("db", "type")
        );
        $ilDB->initFromIniFile($ilClientIniFile);
        if ($ilDB->connect(true)) {
            unset($DIC['ilDB']);
            $DIC['ilDB'] = $ilDB;

            require_once("Services/Administration/classes/class.ilSetting.php");

            $settings = new ilSetting();
            unset($DIC["ilSetting"]);
            $DIC["ilSetting"] = $settings;

            // workaround to determine http path of client
            define("IL_INST_ID", (int) $settings->get("inst_id", '0'));

            $this->xmlStartTag(
                "Client",
                [
                    "inst_id" => $settings->get("inst_id"),
                    "id" => basename($client_directory),
                    'enabled' =>  $ilClientIniFile->readVariable("client", "access") ? "TRUE" : "FALSE",
                    "default_lang" => $ilClientIniFile->readVariable("language", "default")
                ]
            );
            $this->xmlEndTag("Client");
        }
        return true;

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
