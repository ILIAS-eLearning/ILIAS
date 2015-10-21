<?php
include_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilBaseReportPlugin extends ilRepositoryObjectPlugin {

	abstract public function getPluginName();

}