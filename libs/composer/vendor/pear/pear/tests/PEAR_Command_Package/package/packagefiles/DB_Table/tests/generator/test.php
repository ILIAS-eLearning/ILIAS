<?php
require_once 'config.php';

// Set default database (MySQL specific code)
if (!$db_conn) {
    $result = $conn->query("USE $db_name");
    if (PEAR::isError($result)){
        print $result->getMessage()."\n";
    }
}

require_once 'DB/Table/Generator.php';

$generator = new DB_Table_Generator($conn, $db_name);
$generator->class_write_path = $db_name;
#$generator->getTableNames();
$return = $generator->generateTableClassFiles();
if (PEAR::isError($return)) {
    print $return->getMessage();
    die;
}
$generator->generateDatabaseFile();
if (PEAR::isError($return)) {
    print $return->getMessage();
    die;
}

?>
