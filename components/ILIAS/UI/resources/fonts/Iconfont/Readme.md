# il-icons Font
This font contains the ILIAS Glyphset (see: UI Component Symbol/Glyph).
This font-set has been generated [icomoon](https://icomoon.io/app). 

## Changes
Changes (adding/removing/changing Glyphs) can be done by importing the json of the font
contained in this folder and re-generating to font with This font-set has 
been generated [icomoon](https://icomoon.io/app).

**Important** Final Check: Always provide a final check, making sure, that the previous glyphs still in the same place?


### Find entity

1. download JSON with il-icons font 
2. Goto https://icomoon.io/app/
3. left menu > New Empty Set
4. right menu > Import to Set
5. select JSON file
6. below > Generate Font

Now all il-icons entities are visible with their numbers.


### Replace entity

1. download JSON with il-icons font
2. Goto https://icomoon.io/app/
3. left menu > New Empty Set
4. right menu > Import to Set
5. select JSON file
6. bottom menu "Selection" > top right menu > Deselect (no selection)
7. click on the glyph you want to change. (will be selected)
8. switch to "Pen" (edit) in the menu above
9. click on the glyph you want to change (modal opens)
10. modal > button "Replace".
11. select new SVG file.
12. close modal via X (top right corner), no saving necessary

The new SVG file must not have contours, but must have areas. It may be necessary to convert the file from contours 
to surfaces in Illustrator [see below](#convert-glyph).

### Add entity
1. download JSON with il-icons font
2. Goto https://icomoon.io/app/
3. left menu > New Empty Set
4. right menu > Import to Set
5. select JSON file
6. deselect all
7. right menu > Import to Set
8. select file and upload.
9. select menu "Move" in the header.
10. manually move the first entry to the end.

The new SVG file must not have contours, but must have surfaces. The file may have to be converted from outlines 
to areas in Illustrator.

### Export Il-icons font
1. right menu > Select All
2. below > Generate Font
3. bottom > Download Font
4. center menu "Selection" > right menu > Download JSON
5. open ZIP package.
6. rename the files so that they have the same name as in the ILIAS Github repo.

The JSON should be exported so that you can import the JSON the next time you customize the font and make the adjustments 
to the font (without having to register with Icomoon). The ILIAS repo should only contain the json and the woff file. 
The eot is an old file for IE, the ttf does not always render the glyphs correctly as a font. 
Woff or Woff2 (but icomoon does not offer this) would be the required files for the font.

### Convert Glyph

How to convert an outline glyph to area glyph in Illustrator:
1. convert object into a path.
2. menu "Object" > Convert...
3. select "Outline". Ok.
4. "Save as..."
5. save as "SVG".
6. SVG options > not responsive.
7. Click 0k.