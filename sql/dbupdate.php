<#1>
CREATE TABLE glossary_term
(
	id			INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	glo_id		INT NOT NULL,
	term		VARCHAR(200),
	language	CHAR(2),
	INDEX glo_id (glo_id)
);

<#2>
CREATE TABLE glossary_definition
(
	id			INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	term_id		INT NOT NULL,
	page_id		INT NOT NULL
);
