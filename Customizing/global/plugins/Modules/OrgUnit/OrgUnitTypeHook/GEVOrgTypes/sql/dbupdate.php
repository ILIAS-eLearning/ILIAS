<#1>
<?php

require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevOrgUnitUtils::createOrgType(gevSettings::ORG_TYPE_VENUE, "Ort", "Veranstaltungs- und Übernachtungsorte");
gevOrgUnitUtils::createOrgType(gevSettings::ORG_TYPE_PROVIDER, "Anbieter", "Trainingsanbieter");
gevOrgUnitUtils::createOrgType(gevSettings::ORG_TYPE_DEFAULT, "--", "");

?>