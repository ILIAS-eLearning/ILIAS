/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

package de.ilias.services.transformation;

import de.ilias.services.settings.ServerSettings;
import org.apache.fop.apps.FopConfParser;
import org.apache.fop.apps.FopFactory;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.xml.sax.SAXException;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.URI;
import java.net.URISyntaxException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardCopyOption;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

public class FOConfigBuilder
{
    private final Logger logger = LogManager.getLogger(this.getClass().getName());

    private ServerSettings settings;

    private static String fopConfigTemplate = """
            <?xml version="1.0" ?>
            <fop version="1.0">
              <strict-configuration>true</strict-configuration>
              <use-cache>false</use-cache>
              <renderers>
                <renderer mime="application/pdf">
                  <fonts>
                    %s
                  </fonts>
                </renderer>
              </renderers>
            </fop>
           """.stripLeading();
    private List<Path> fontDirectories = new ArrayList<>();
    private Path tempDirectory = null;

    public FOConfigBuilder(ServerSettings settings) {
        this.settings = settings;
    }

    public FopFactory buildFopFactory() throws IOException, TransformationException
    {
        String directoryEntries = "";
        String fopXmlConfig = "";

        initDirectories();
        directoryEntries = fontDirectories.stream()
                .map(p -> "<directory>%s</directory>".formatted(p.toString()))
                .collect(Collectors.joining("\n"));
        fopXmlConfig = fopConfigTemplate.formatted(directoryEntries);
        logger.info("Using config {}", fopXmlConfig);

            InputStream in = new ByteArrayInputStream(
                    fopXmlConfig.getBytes(StandardCharsets.UTF_8)
            );
        try {
            return new FopConfParser(
                    in,
                    tempDirectory.toUri()
            )
                    .getFopFactoryBuilder().build();
        } catch (SAXException e) {
            logger.error("Cannot configure FOP environment", e);
            throw new TransformationException(e);
        }
    }

    private void initDirectories() throws TransformationException
    {
        try {
            initUnifont();
            initCustomDirectory();
        } catch (IOException e) {
            throw new TransformationException(e);
        }
    }

    private void initUnifont() throws IOException {
        tempDirectory = Files.createTempDirectory("ilias-fonts-");
        Path tempFont = tempDirectory.resolve("unifont.ttf");
        tempDirectory.toFile().deleteOnExit();
        InputStream in = FO2PDF.class.getResourceAsStream("/de/ilias/config/fonts/unifont.ttf");
        Files.copy(in, tempFont, StandardCopyOption.REPLACE_EXISTING);

        fontDirectories.add(tempDirectory);
    }

    private void initCustomDirectory()
    {
        if (settings.getFopFontDirectory() == null) {
            logger.info("No custom fonts configured");
            return;
        }
        logger.info("Using font directory {}", settings.getFopFontDirectory());
        fontDirectories.add(settings.getFopFontDirectory());
    }
}
