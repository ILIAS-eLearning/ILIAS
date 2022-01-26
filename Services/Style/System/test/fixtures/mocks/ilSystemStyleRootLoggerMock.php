<?php declare(strict_types=1);

class ilSystemStyleRootLoggerMock implements ilLoggerInterface
{
    public function isHandling($a_level){}
    
    public function log($a_message, $a_level = ilLogLevel::INFO){}
    
    public function dump($a_variable, $a_level = ilLogLevel::INFO){}
    
    public function debug($a_message, $a_context = []){}
    
    public function info($a_message){}
    
    public function notice($a_message){}
    
    public function warning($a_message){}
    
    public function error($a_message){}
    
    public function critical($a_message){}
    
    public function alert($a_message){}
    
    public function emergency($a_message){}
    
    public function getLogger(){}
    
    public function write($a_message, $a_level = ilLogLevel::INFO){}
    
    public function writeLanguageLog($a_topic, $a_lang_key){}
    
    public function logStack($a_level = null, $a_message = ''){}
    
    public function writeMemoryPeakUsage($a_level){}
    
}
