<?php
// chdir("../../../");
// require_once("./Services/Init/classes/class.ilInitialisation.php");
// ilInitialisation::initILIAS();

$result = [];
foreach ($_FILES['file']['tmp_name'] as $file) {
    $result[] = md5(rand(0, 1000));
}
echo json_encode($result);

