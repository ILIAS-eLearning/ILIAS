
# About Excel Service [WIP]

ILIAS Excel Service provides a set of classes that allow read and write different spreadsheet file formats like Excel, LibreOffice etc.

This service uses an adapter interface to map behaviour from the external class https://github.com/PHPOffice/PhpSpreadsheet to ILIAS.
 
## Updates and changes

(Oct 24, 2018) ILIAS 5.4 migrated from the deprecated PHPExcel to PhpSpreadsheet. To avoid any change in consumer classes of this service,
all changes were done in the adapter class ./Services/Excel/classes/class.ilExcel.php

# Common issues

## Column indexes

Both PHPExcel and PHPSpreadsheet index the rows based on 1,  but they start with different column index.

The former PHPExcel library was starting column indexes from value 0 but in PHPSpreadsheet the column indexes are now based on 1.

For this reason and to avoid any further refactoring in other components, the adapter class has to add 1 to the column number in some methods.

For the sake of example, method setCell which is affected for this issue, is called more than 200 times.

ilExcel.php

    public function setCell($a_row, $a_col, $a_value)

ilRatingCategoryGUI starting from non-existent 0 column position

    $row = 1;
    $excel->setCell($row, 0, $this->export_subobj_title." (".$lng->txt("id").")");
    $excel->setCell($row, 1, $this->export_subobj_title);

The column index adjustment is done by the method "columnIndexAdjustment". If any other adjustment is needed for this matter, it must
be there.

# External documentation

## PHPSpreadsheet documentation
- https://github.com/PHPOffice/PhpSpreadsheet
- https://phpspreadsheet.readthedocs.io/en/latest/

## Column index issue
- https://stackoverflow.com/questions/41117762/what-are-the-main-differences-between-phpexcel-and-phpspreadsheet?rq=1 (gibberish answer) 
- https://github.com/PHPOffice/PhpSpreadsheet/issues/273

## Bugs and limitations
- Worksheet name only allows 31 chars:

    https://mantis.ilias.de/view.php?id=19056
    
    https://github.com/PHPOffice/PHPExcel/issues/79 
    
    (Fixed?) https://github.com/PHPOffice/PhpSpreadsheet/pull/186

- Invalid characters:
    
    https://mantis.ilias.de/view.php?id=20749
    
    https://social.msdn.microsoft.com/Forums/en-US/84c0c4d2-52b9-4502-bece-fdc616db05f8/invalid-characters-for-excel-sheet-names?forum=vsto
    
        *
        :
        /
        \
        ?
        [
        ]
        '-
        '
    