<?php

// DSN string parts (change as needed)
$rdbms    = 'mysqli';
$username = 'root';
$password = 'password';
$hostname = 'localhost';

// Database name:
// Do NOT use an existing database for DB_TABLE_DATABASE unit tests //
// Do use an existing database for DB_TABLE_Generator test
$db_name = 'TestDB1';

// Choose MDB2 or DB
$backend  = 'DB'; // 'DB' or 'MDB2', capitalized

// Connection mode (change as needed)
// Set db_conn true to connect directly to an existing database
// Set false to connect to RDBMS and then create the database.
// Set false for MySQL
$db_conn = false;

// Verbosity of unit test output
// (-1 for silent, 0 for method names, 1 for some data, 2 for more)
$verbose = 2;

// ---------- Do not change below this line -----------------------

// Create DSN string
$dsn = "$rdbms://$username:$password@$hostname";
if ($db_conn) {
    $dsn = "$dsn/$db_name";
} 

// Connect to RDBMS, $conn is DB/MDB2 object
if ($backend == 'DB') {
    require_once 'DB.php';
    $conn =& DB::connect($dsn);
    if (DB::isError($conn)) {
        print $conn->getMessage()."\n";
        die;
    }
} elseif ($backend == 'MDB2') {
    require_once 'MDB2.php';
    $conn =& MDB2::factory($dsn);
    if (PEAR::isError($conn)) {
        print "\n" . "Failure to connect by MDB2";
        print "\n" . $conn->getMessage();
        die;
    }
}

?>
