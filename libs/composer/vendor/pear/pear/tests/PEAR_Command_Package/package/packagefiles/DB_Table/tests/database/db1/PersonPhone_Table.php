<?php

class PersonPhone_Table extends DB_Table 
{
    var $col = array(
        'PersonID' => array('type' => 'integer', 'default' => 75),
        'PhoneID'  => array('type' => 'integer', 'require' => true)
        );
    var $idx = array(
        'PersonID' => array('cols' => 'PersonID', 'type' => 'normal'),
        'PhoneID'  => array('cols' => 'PhoneID', 'type' => 'normal')
        );
}

?>
