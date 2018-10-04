<?php
require 'db1/create.php';
require 'db1/data.php';

$PersonID  = 0;
$AddressID = 0;
$StreetID  = 0;
$PhoneID   = 0;

// PHP arrays that contain same data as RDBMS tables
$person         = array();
$address        = array();
$phone          = array();
$person_address = array();
$person_phone   = array();
$street         = array();
$street_ids     = array();

foreach ($data as $data_row) {

    // Insert into $DataFile
    $result = $DataFile->insert($data_row);
    if (PEAR::isError($result)){
        print $result->getMessage()."\n";
    }

    // Create and insert Person record
    $person_row = array();
    foreach ( $Person->col as $col => $col_def ){
        if ( key_exists($col, $DataFile->col)) {
            $person_row[$col] = $data_row[$col];
        } else {
            $person_row[$col]=null;
        }
    }
    $PersonID = $PersonID + 1;
    $result = $db->insert('Person', $person_row);
    $person_row['PersonID'] = $PersonID;
    $person[] = $person_row;
    if (PEAR::isError($result)){
        print $result->getMessage()."\n";
    } else {

        // Create and insert Street record
        $street_row = array();
        foreach ( $Street->col as $col => $col_def ){
            if ( key_exists($col, $data_row)) {
                $street_row[$col]=$data_row[$col];
            }
        }
        $street_id = 
          "{$street_row['Street']}_{$street_row['City']}_{$street_row['StateAbb']}";
        if (!in_array($street_id, $street_ids)) {
            $StreetID = $StreetID + 1;
            $result = $db->insert('Street', $street_row);
            $street[] = $street_row;
            $street_ids[] = $street_id;
        }

        // Create and insert Address record
        $address_row = array();
        foreach ( $Address->col as $col => $col_def ){
            if ( key_exists($col, $data_row)) {
                $address_row[$col] = $data_row[$col];
            #} else {
            #    $address_row[$col]=null;
            }
        }
        $AddressID = $AddressID + 1;
        $result = $db->insert('Address', $address_row);
        $address_row['AddressID'] = $AddressID;
        $address[] = $address_row;
        if (PEAR::isError($result)){
            print $result->getMessage()."\n";
        } else {
            $assoc = array();
            $assoc['PersonID2'] = $PersonID;
            $assoc['AddressID'] = $AddressID;
            $result = $db->insert('PersonAddress', $assoc);
            $person_address[] = $assoc;
            if (PEAR::isError($result)){
                print $result->getMessage()."\n";
            }
        }

        // Create and insert Phone record
        $phone_row = array();
        foreach ( $Phone->col as $col => $col_def ){
            if ( key_exists($col, $data_row)) {
                $phone_row[$col]=$data_row[$col];
            #} else {
            #    $phone_row[$col]=null;
            }
        }
        if (!is_null($phone_row['PhoneNumber'])) {
            $PhoneID = $PhoneID + 1;
            $result = $db->insert('Phone', $phone_row);
            $phone_row['PhoneId'] = $PhoneID;
            $phone[] = $phone_row;
            if (PEAR::isError($result)){
                print $result->getMessage()."\n";
            } else {
                // Insert PersonPhone Row
                $assoc = array();
                $assoc['PersonID'] = $PersonID;
                $assoc['PhoneID']  = $PhoneID;
                $result = $db->insert('PersonPhone', $assoc);
                $person_phone[] = $assoc;
                if (PEAR::isError($result)){
                    print $result->getMessage()."\n";
                }
            }
        }
    }
}

$table_arrays = array('Person' => $person,
                      'Address' => $address,
                      'Phone' => $phone,
                      'PersonAddress' => $person_address,
                      'PersonPhone' => $person_phone,
                      'Street' => $street);

?>
