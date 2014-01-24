<?php

$ilDB->query("DELETE FROM page_layout");

$ilDB->query("DELETE FROM page_object WHERE(parent_type='stys');");


$ilDB->query("INSERT INTO page_layout(layout_id,title,description,active) values (1,'1A Simple text page with accompanying media','Example description',1);");

$ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
values (1,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"500px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");

//second

$ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (2,'1C Text page with accompanying media and test',1);");

$ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
values (2,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");

//third

$ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (3,'1E Text page with accompanying media followed by test and text',1);");

$query = "SELECT LAST_INSERT_ID() as id";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

$ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
values (3,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent><PageContent PCID=\"9b77eb1d8a478197d69b99d938fea8f\"><PlaceHolder ContentClass=\"Text\" Height=\"200px\"/></PageContent></TableData><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData> </TableRow></Table></PageContent></PageObject>');");


//fourth

$ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (4,'2C Simple media page with accompanying text and test',1);");

$query = "SELECT LAST_INSERT_ID() as id";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

$ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
values (4,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"46ac4936082485f457c7041278b5c5f5\"><PageContent PCID=\"2e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Media\" Height=\"300px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"66%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent><PageContent PCID=\"3f77eb1d8a478493d69b99d438fda8f\"><PlaceHolder ContentClass=\"Question\" Height=\"200px\"/></PageContent></TableData></TableRow></Table></PageContent></PageObject>');");

//fifth


$ilDB->query("INSERT INTO page_layout(layout_id,title,active) values (5,'7C Vertical component navigation page with media and text',1);");

$query = "SELECT LAST_INSERT_ID() as id";
$res = $ilDB->query($query);
$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

$ilDB->query("INSERT INTO page_object(page_id,parent_type,content)
values (5,'stys','<PageObject><PageContent PCID=\"9f77db1d8a478497d69b99d938faa8ff\"><Paragraph Language=\"en\" Characteristic=\"Headline1\">Headline 1</Paragraph></PageContent><PageContent PCID=\"134d24457cbc90ea1bf1a1323d7c3a89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"ccade07caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"1f77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Text\" Height=\"300px\"/></PageContent></TableData> </TableRow><TableRow PCID=\"efade08caf9fd13e8c7012f29c9510be\"><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a4\" Width=\"100%\"><PageContent PCID=\"124d24457cbc90ea1bf1a1323d7c3b89\"><Table Language=\"en\" Border=\"0px\" CellPadding=\"2px\" CellSpacing=\"0px\" HorizontalAlign=\"Left\" Width=\"100%\"><TableRow PCID=\"dfade09caf9fd13e8c7012f29c9510be\"><TableData PCID=\"e4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3e77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4e77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"a4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3a77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4ea7eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData><TableData PCID=\"b4e417c08feebeafb1487e60a2e245a5\" Width=\"33%\"><PageContent PCID=\"3b77eb1d8a478497d69b99d938fda8f\"><PlaceHolder ContentClass=\"Media\" Height=\"150px\"/></PageContent><PageContent PCID=\"4b77eb1d8a478497d69b99d938fda8e\"><PlaceHolder ContentClass=\"Text\" Height=\"250px\"/></PageContent></TableData></TableRow></Table></PageContent></TableData></TableRow></Table></PageContent></PageObject>');");

