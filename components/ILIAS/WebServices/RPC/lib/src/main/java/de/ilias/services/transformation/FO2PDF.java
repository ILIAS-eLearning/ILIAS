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


import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.ServerSettings;
import org.apache.fop.apps.*;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

import javax.xml.transform.*;
import javax.xml.transform.sax.SAXResult;
import javax.xml.transform.stream.StreamSource;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URLConnection;
import java.net.URISyntaxException;
import java.io.*;
import java.nio.charset.StandardCharsets;
import java.util.List;
import java.util.Objects;

public class FO2PDF
{

    private static FO2PDF instance = null;

    private final Logger logger = LogManager.getLogger(this.getClass().getName());
    private String foString = null;
    private byte[] pdfByteArray = null;
    private FopFactory fopFactory = null;

    /**
     * Singleton constructor
     */
    public FO2PDF() throws TransformationException
    {
        try {
            FOConfigBuilder configBuilder = new FOConfigBuilder(ServerSettings.getInstance());
            fopFactory = configBuilder.buildFopFactory();
        } catch (IOException | ConfigurationException e) {
            logger.error("FOP configuration error", e);
            throw new TransformationException(e);
        }
    }

    /**
     * Get FO2PDF instance
     */
    public static FO2PDF getInstance() throws TransformationException
    {
        if (instance == null) {
            return instance = new FO2PDF();
        }
        return instance;
    }

    /**
     * clear fop uri cache
     */
    public void clearCache()
    {
        fopFactory.getImageManager().getCache().clearCache();
    }

    public void transform()
            throws TransformationException
    {

        try {

            logger.info("Starting fop transformation...");

            FOUserAgent foUserAgent = fopFactory.newFOUserAgent();
            ByteArrayOutputStream out = new ByteArrayOutputStream();

            foUserAgent.getEventBroadcaster().addEventListener(event -> {
                try {
                    logger.warn(
                        "FOP EVENT: group={}, id={}",
                        event.getEventGroupID(),
                        event.getEventID()
                    );

                    event.getParams().forEach((k, v) -> {
                        logger.warn("  {} = {}", k, v);
                        if (Objects.equals(k, "uri") && v instanceof String uriString) {
                            try {
                                logUriFopDiagnostics(new URI(uriString));
                            } catch (URISyntaxException | IOException e) {
                                logger.error("Error while probing URI", e);
                            }
                        }
                    });
                } catch (Exception e) {
                    logger.error("Error while logging FOP event", e);
                }
            });

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
    public String getFoString()
    {
        return foString;
    }


    /**
     * @param foString The foString to set.
     */
    public void setFoString(String foString)
    {
        this.foString = foString;
    }

    public byte[] getPdf()
    {
        return this.pdfByteArray;
    }

    public void setPdf(byte[] ba)
    {
        this.pdfByteArray = ba;
    }

    private void logUriFopDiagnostics(URI uri) throws IOException {
        logger.warn("Trying to load URI which could not be processed by FOP: {}", uri);

        URLConnection connection = uri.toURL().openConnection();
        if (!(connection instanceof HttpURLConnection conn)) {
            logger.warn("URI does not point to an HTTP resource: {}", uri);
            return;
        }
        conn.setInstanceFollowRedirects(false);
        conn.setConnectTimeout(15000);
        conn.setReadTimeout(15000);

        try {
            int code = conn.getResponseCode();
            logger.warn("HTTP status: {}", code);

            conn.getHeaderFields().forEach((key, values) -> {
                logger.warn("Header: {} = {}", key, values);
            });

            InputStream stream;
            if (code >= 200 && code < 300) {
                stream = conn.getInputStream();
            } else {
                stream = conn.getErrorStream();
                if (stream == null) {
                    logger.warn("HTTP error {} with no body", code);
                    return;
                }
            }

            try (stream) {
                ByteArrayOutputStream buffer = new ByteArrayOutputStream();
                byte[] data = new byte[4096];
                int n;
                while ((n = stream.read(data)) != -1) {
                    buffer.write(data, 0, n);
                }
                String bodyPreview = buffer.toString(StandardCharsets.UTF_8);
                logger.warn(
                    "Response body (first 1000 chars):\n{}",
                    bodyPreview.substring(0, Math.min(1000, bodyPreview.length()))
                );
            }
        } finally {
            conn.disconnect();
        }
    }

    private InputStream getFoInputStream()
    {
        return new ByteArrayInputStream(getFoString().getBytes(StandardCharsets.UTF_8));
    }
}
