<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
								xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
								xmlns:xhtml="http://www.w3.org/1999/xhtml">

	<xsl:output method="html"/>

	<!-- strip white spaces between tags -->
	<xsl:strip-space elements="*"/>

	<xsl:template match="ContentObject">
		<html>
			<body>
				<xsl:call-template name="MetaData" />
				<xsl:call-template name="Stucture" />
			</body>
		</html>
	</xsl:template>
	
	<xsl:template name="MetaData" match="/ContentObject/MetaData">
		<h1><xsl:value-of select="General/Title" /></h1>
	</xsl:template>
	<xsl:template name="Stucture" match="/Stucture/MetaData">
		
	</xsl:template>
	
</xsl:stylesheet>
