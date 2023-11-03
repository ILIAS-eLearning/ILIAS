/*
+-----------------------------------------------------------------------------------------+
| ILIAS open source                                                                       |
+-----------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne                        |
|                                                                                         |
| This program is free software; you can redistribute it and/or                           |
| modify it under the terms of the GNU General Public License                             |
| as published by the Free Software Foundation; either version 2                          |
| of the License, or (at your option) any later version.                                  |
|                                                                                         |
| This program is distributed in the hope that it will be useful,                         |
| but WITHOUT ANY WARRANTY; without even the implied warranty of                          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           |
| GNU General Public License for more details.                                            |
|                                                                                         |
| You should have received a copy of the GNU General Public License                       |
| along with this program; if not, write to the Free Software                             |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             |
+-----------------------------------------------------------------------------------------+
*/

package de.ilias.services.transformation;


import org.apache.fop.apps.*;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.xml.sax.SAXException;

import javax.xml.transform.*;
import javax.xml.transform.sax.SAXResult;
import javax.xml.transform.stream.StreamSource;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.InputStream;
import java.net.URISyntaxException;
import java.nio.charset.StandardCharsets;
import java.util.List;

public class FO2PDF {

    private static FO2PDF instance = null;

    private final Logger logger = LogManager.getLogger(this.getClass().getName());
    private String foString = null;
    private byte[] pdfByteArray = null;
    private FopFactory fopFactory = null;

    /**
     * Singleton constructor
     */
    public FO2PDF() {
        try {
            fopFactory = FopFactory.newInstance(getClass().getResource("/de/ilias/config/fopConfig.xml").toURI());
            fopFactory.getFontManager().deleteCache();
            fopFactory.getFontManager().saveCache();

        } catch (SAXException | URISyntaxException | NullPointerException ex) {
            logger.error("Cannot load fop configuration:" + ex);
        }

    }

    /**
     * Get FO2PDF instance
     */
    public static FO2PDF getInstance() {

        if (instance == null) {
            return instance = new FO2PDF();
        }
        return instance;
    }

    /**
     * clear fop uri cache
     */
    public void clearCache() {

        fopFactory.getImageManager().getCache().clearCache();
    }

    public void transform()
            throws TransformationException {

        try {

            logger.info("Starting fop transformation...");

            FOUserAgent foUserAgent = fopFactory.newFOUserAgent();
//            foUserAgent.setTargetResolution(300);
            ByteArrayOutputStream out = new ByteArrayOutputStream();

            Fop fop = fopFactory.newFop(MimeConstants.MIME_PDF, foUserAgent, out);

//          Setup JAXP using identity transformer
            TransformerFactory factory = TransformerFactory.newInstance();
            Transformer transformer = factory.newTransformer(); // identity transformer

            Source src = new StreamSource(getFoInputStream());
            Result res = new SAXResult(fop.getDefaultHandler());

            transformer.transform(src, res);

            FormattingResults foResults = fop.getResults();
            List pageSequences = foResults.getPageSequences();
            for (Object pageSequence : pageSequences) {
                PageSequenceResults pageSequenceResults = (PageSequenceResults) pageSequence;
                logger.debug("PageSequence "
                        + (String.valueOf(pageSequenceResults.getID()).length() > 0
                        ? pageSequenceResults.getID() : "<no id>")
                        + " generated " + pageSequenceResults.getPageCount() + " pages.");
            }
            logger.info("Generated " + foResults.getPageCount() + " pages in total.");

            this.setPdf(out.toByteArray());

        } catch (TransformerConfigurationException e) {
            logger.warn("Configuration exception: " + e);
            throw new TransformationException(e);
        } catch (TransformerException e) {
            logger.warn("Transformer exception: " + e);
            throw new TransformationException(e);
        } catch (FOPException e) {
            throw new TransformationException(e);
        }
    }


    /**
     * @return Returns the foString.
     */
    public String getFoString() {
        return foString;
    }


    /**
     * @param foString The foString to set.
     */
    public void setFoString(String foString) {
        this.foString = foString;
    }

    public byte[] getPdf() {
        return this.pdfByteArray;
    }

    public void setPdf(byte[] ba) {
        this.pdfByteArray = ba;
    }


    private InputStream getFoInputStream() {
        return new ByteArrayInputStream(getFoString().getBytes(StandardCharsets.UTF_8));
    }
}
