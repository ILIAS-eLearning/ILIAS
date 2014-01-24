/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package de.ilias.services.lucene.index.transform;

import de.ilias.services.object.DataSourceFactory;
import java.util.Arrays;
import java.util.List;
import org.apache.log4j.Level;
import org.apache.log4j.Logger;

/**
 *
 * @author stefan
 */
public class MimeTypeExtractor implements ContentTransformer {

	protected static Logger logger = Logger.getLogger(MimeTypeExtractor.class);

	private static final String MIME_DEFAULT = "other";
	private static final String MIME_DOC = "word";
	private static final String MIME_EXCEL = "excel";
	private static final String MIME_POWERPOINT = "powerpoint";
	private static final String MIME_IMAGE = "image";
	private static final String MIME_PDF = "pdf";
	
	
	
	/**
	 * all doc types
	 */
	private static final  List<String> MIME_DOC_LIST = Arrays.asList(
		"odt",
		"ott",
		"sxw",
		"fodt",
		"stw",
		"uot",
		"docx",
		"doc",
		"rtf"
	);
	
	private static final List<String> MIME_EXCEL_LIST = Arrays.asList(
		"ods",
		"ots",
		"sxc",
		"stc",
		"fods",
		"uos",
		"xlsx",
		"xls",
		"xlt",
		"csv"
	);
	
	private static final List<String> MIME_POWERPOINT_LIST = Arrays.asList(
		"odp",
		"otp",
		"odg",
		"sxi",
		"sti",
		"sxg",
		"fodp",
		"uop",
		"pptx",
		"ppsx",
		"potm",
		"ppt",
		"pps"
	);
	
	private static final List<String> MIME_IMAGE_LIST = Arrays.asList(
		"jpg",
		"jpeg",
		"gif",
		"xcf",
		"ico",
		"png",
		"psd",
		"tif",
		"tiff",
		"bmp",
		"bitmap",
		"ico"
	);
	
	
	private static final List<String> MIME_PDF_LIST = Arrays.asList(
		"pdf"
	);
		
	
	/**
	 * Default contructor
	 */
	public MimeTypeExtractor() {
		
	}
	
	
	/**
	 * Extract a simple mime type
	 * @param content
	 * @return 
	 */
	public String transform(String content) {
		
		logger.setLevel(Level.DEBUG);
		
		// no dot
		if(content.lastIndexOf(".") < 0) {
			logger.debug("No dot found for " + content);
			return MimeTypeExtractor.MIME_DEFAULT;
		}

		String extension = content.substring(content.lastIndexOf(".") + 1);
		logger.debug("Extension is " + extension);
		if(MIME_DOC_LIST.contains(extension.toLowerCase())) {
			logger.info("Found mime " + MIME_DOC + " for " + content);
			return MIME_DOC;
		}
		if(MIME_EXCEL_LIST.contains(extension.toLowerCase())) {
			logger.info("Found mime " + MIME_EXCEL + " for " + content);
			return MIME_EXCEL;
		}
		if(MIME_POWERPOINT_LIST.contains(extension.toLowerCase())) {
			logger.info("Found mime " + MIME_POWERPOINT + " for " + content);
			return MIME_POWERPOINT;
		}
		if(MIME_IMAGE_LIST.contains(extension.toLowerCase())) {
			logger.info("Found mime " + MIME_IMAGE + " for " + content);
			return MIME_IMAGE;
		}
		if(MIME_PDF_LIST.contains(extension.toLowerCase())) {
			logger.info("Found mime " + MIME_PDF + " for " + content);
			return MIME_PDF;
		}
		logger.info("No suitable extension found for " + content);
		return MimeTypeExtractor.MIME_DEFAULT;
	}
}
