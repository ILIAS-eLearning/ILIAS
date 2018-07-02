# Excel wrapper

*A wrapper for a streaming excel-writer.*

**Contacts:** [Richard Klees](https://github.com/klees)

## Writing files

An excel-write has to implement a kind of functionality as defined.

* Create a writer object
* Define style for single column at any time
* Fill the sheet row by row
* Create new sheets and switch by name
* Save to given folder and filename

To fill the sheet row by row is a recommended feature to increase the speed on mass export of data. It will not be possible to step back to rows or single columns to change values or something else.

```php
public function addRow(array $values) {
    $this->writer->addRow($values);
}
```

For designing the sheet, it is possible to give any column at any time a new style. E.g. in row 1 to 5 the colum A is bold and left orientated. Starting with row 6 it will only left orientated and not longer bold. This gives the opportunity to design column headers or highlight special values.

```php
public function setColumnStyle(Style $style, $column) {
    $this->writer->setColumnStyle($column, $style);
};
```

It is possible to create new sheets and switch them by name. If you creating a new sheet, it will be automaticly the current.

```php
public function createSheet($name) {
    $this->writer->createSheet($name);
}

Public function selectSheet($name) {
    $this->writer->setCurrentSheet($name);
}
```

## Define Styles

A column can be styled in a lot of ways. This wrapper includes the mostly used.

* Font family
* Font size
* Bold
* Italic
* Underline
* Text color
* Background color
* Orientation
* Border
* Border color

```php
public function getNewStyle() {
    $style = new Style();
    $style ->setFontFamily('Aarial')
           ->setFontSize(12)
           ->setItalic(false)
           ->setBorder(Style::TOP);

    return $style;
}
```

## Example for use

This is a small example for using this wrapper.

```php
public function exportData($file_name, $file_path, array $header, array $values) {
    $writer = new Writer();
    $writer->setFileName($file_name);
    $writer->setPath($file_path);

    $header_style = $this->getHeaderStyle();
    $writer->setColumnStyle('A', $header_style);
    $write->addRow($header);

    $bold_style = $this->getBoldStyle();
    $basic_style = $this->getBasicStyle();

    $writer->setColumnStyle('A', $bold_style);
    $writer->setColumnStyle('B', $basic_style);
    foreach($values as $value) {
        $write->addRow($value);
    }

    $writer->setColumnStyle('A', $basic_style);
    $writer->setColumnStyle('B', $bold_style);
    foreach($values as $value) {
        $write->addRow($value);
    }

    $writer->saveFile();
    $write->close();
}

protected function getHeaderStyle() {
    $style = new Style()
    $style->setBold(true)
          ->setItalic(true);

    return $style;
}

protected function getBoldStyle() {
    $style = new Style()
    $style->setBold(true)

    return $style;
}

protected function getBsicStyle() {
    return new Style();
}
```

The xlsx sheet result might be look like this.

R\C | A | B
------------ | ------------- | ------------
1 | **_Content for header_**
2 | **Content in the first column** | Content in the second column
3 | **Content in the first column** | Content in the second column
4 | Content in the first column | **Content in the second column**
5 | Content in the first column | **Content in the second column**