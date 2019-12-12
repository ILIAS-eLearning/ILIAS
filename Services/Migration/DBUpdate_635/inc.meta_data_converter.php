<?php

function ilMDConvert($table, $fields, $key)
{
    global $ilDB;

    $where = "WHERE ";
    $where .= implode(" LIKE '%\%' ESCAPE '�' OR ", ilUtil::quoteArray($fields));
    $where .= " LIKE '%\\%' ESCAPE '�'";

    $query = "SELECT * FROM " . $table . " " .
        $where;

    $res = $ilDB->query($query);
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $query = "UPDATE $table ";

        $counter = 0;
        foreach ($fields as $field) {
            if ($counter++) {
                $query .= ", ";
            } else {
                $query .= "SET ";
            }

            $query .= ($field . " = " . $ilDB->quote(stripslashes($row->$field)) . " ");
        }
        $query .= (" WHERE " . $key . " = " . $ilDB->quote($row->$key));

        // Perform the query
        $ilDB->query($query);
    }
}
