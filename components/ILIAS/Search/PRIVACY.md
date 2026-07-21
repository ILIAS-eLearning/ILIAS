# Search
Disclaimer: This documentation does not guarantee completeness or correctness. Please report any missing or incorrect information using the ILIAS issue tracker or contribute a fix via Pull Request (docs/development/contributing.md#pull-request-to-the-repositories).
## General Information
The Search service in ILIAS is designed to enable efficient access to a wide variety of content, users, and resources within the platform.
There are three types of searches in ILIAS: standard search, user search and advanced search. 
Standard Search can be set to direct search or Lucene: 
- Direct search only allows to search for Titles and descriptions of objects without using additional software. The content of files (e.g. PDFs or HTML learning modules) or content of objects is not recorded and therefore not displayed as a search result.
- Advanced search additionally search LOM and Custom Metadata. These results may comprise personal data.  
- Lucene search is an index-based search type. It searches both the ILIAS database and files. Activating this type of search is recommended for larger ILIAS installations. To use the Lucene search in ILIAS, the JAVA-based ilServer must be installed and configured. 
- User search needs Lucene to find people. User search results comprise personal data. 

## Integrated Services
The Search service employs the following services, please consult the respective privacy.mds: 
- Metadata (only if Advanced Search is activated, take out if deprecated) 
- Access Control
- User (only if Lucene is activated)
- Mail (only if Lucene is activated)
- Object Service (requires owner id to be indexed, should be explained there) 

## Data being stored
The Search service itself does not produce personal data but only stores the last search result of any given user. User ID and query and results are saved. This offers convenience. 
For Lucene search, the content data are stored only after updating Lucene search index under Administration> System and Maintenance> General Settings>Cron Job> Update Lucene search index. What is indexed specifically is the responsibility of the components. 
Disabling the personal profile automatically triggers a new indexing run of said users data. The user search will immediately exclude this person from the results. 
Publishing  the personal profile is not effective immediately but will only become effective for the search after the next index run. 

## Data being presented
- Standard search - direct search: Search results present only titles and descriptions of objects
- Standard search - Lucene search: Search results presents titles and descriptions of objects and snippets of content 
- User search: Personal data such as first name, last name, login name and email address may be displayed as search results. What is presented specifically depends on the Personal Profile being published with data fields needing individual activation 
- Personal data of the User object are configured in User service: Which data fields are offered for activation depends on these fields are marked as “Searchable” under Administration >User and Roles> User Management> Settings> Standard Fields / Custom Fields 
- Advanced search: Object results, based on the LOM filter elements and/or all user defined metadata preset here: Administration> Search and Find> Search> Advanced Search (delete if deprecated). Some LOM fields are intended to capture personal data like Author. 

## Data being deleted
The Search itself does not store or delete any personal data.
If the user is deleted, the last search results will be deleted with the user. Index data will be deleted with the next cron-scheduled indexing run, however the search will no longer present the data set. 

## Data being exported
The Search itself does not have an export. 