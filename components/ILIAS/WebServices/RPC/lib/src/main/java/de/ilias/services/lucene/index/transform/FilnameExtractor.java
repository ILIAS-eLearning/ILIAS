/**
 * 
 */
package de.ilias.services.lucene.index.transform;

/**
 * @author shiva
 *
 */
public class FilnameExtractor implements ContentTransformer {

	/**
	 * 
	 */
	public FilnameExtractor() {
	}

	/**
	 * Extract filename from file object title.
	 * Removes the file extension
	 * @see de.ilias.services.lucene.index.transform.ContentTransformer#transform(java.lang.String)
	 */
	public String transform(String content) {

		if(content.lastIndexOf(".") > 0) {
			return content.substring(0,content.lastIndexOf("."));
		}
		return content;
	}
}
