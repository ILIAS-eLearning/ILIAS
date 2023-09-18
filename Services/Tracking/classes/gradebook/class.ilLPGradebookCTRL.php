<?php
chdir($_SERVER['DOCUMENT_ROOT']);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebookWeight.php");
include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebookGrade.php");

switch ($_POST['action'])
{
    case "saveGradebookWeight":
        try{
            $obj_id = ilObject::_lookupObjectId($_POST['ref_id']);
            $gradebookObj=new ilLPGradebookWeight($obj_id);
            $result = $gradebookObj->saveGradebookWeight($_POST['nodes'], $_POST['passing_grade']);
            echo json_encode(['status'=>'success','data'=>$result]);
        }catch(Exception $e){
            echo json_encode(['status'=>'failure','message'=>$e->getMessage()]);
        }
        break;
    case "getGradesForUser":
        try{
            $obj_id = ilObject::_lookupObjectId($_POST['ref_id']);
            $gradebookObj = new ilLPGradebookGrade($obj_id);
            $result = $gradebookObj->getUsersGrades($_POST['usr_id']);
            echo json_encode(['status'=>'success','data'=>$result]);
        }catch(Exception $e){
            echo json_encode(['status'=>'failure','message'=>$e->getMessage()]);
        }
        break;
    case "saveUsersGrades":
        try{
            $obj_id = ilObject::_lookupObjectId($_POST['ref_id']);
            $gradebookObj = new ilLPGradebookGrade($obj_id);
            $result = $gradebookObj->saveUsersGrades($_POST['user_id'],$_POST['grades'],$_POST['overall_status']);
            echo json_encode(['status'=>'success','data'=>$result]);
        }catch(Exception $e){
            echo json_encode(['status'=>'failure','message'=>$e->getMessage()]);
        }
        break;
    case "updateStatus":
        try{
            $obj_id = ilObject::_lookupObjectId($_POST['ref_id']);
            $gradebookObj = new ilLPGradebookGrade($obj_id);
            $result = $gradebookObj->updateStatus($_POST['user_id'],$_POST['overall_status']);
            echo json_encode(['status'=>'success','data'=>$result]);
        }catch(Exception $e){
            echo json_encode(['status'=>'failure','message'=>$e->getMessage()]);
        }
        break;
    case "changeRevision":
        try{
            $obj_id = ilObject::_lookupObjectId($_POST['ref_id']);
            $gradebookObj = new ilLPGradebookGrade($obj_id);
            $result = $gradebookObj->changeUsersRevision($_POST['usr_id'],$_POST['old_revision'],$_POST['new_revision']);
            echo json_encode(['status'=>'success','data'=>$result]);
        }catch(Exception $e){
            echo json_encode(['status'=>'failure','message'=>$e->getMessage()]);
        }
        break;
}



?>
