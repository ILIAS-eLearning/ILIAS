# Meta Data Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

Profile data is only stored in metadata-supporting objects and can therefore be viewed via the "Metadata" tab. 
The Lifecycle and Meta-Metadata LOM metadata sections provide input fields for maintaining contributor data.
With the exception of "Survey" and "Test" objects, no personal data is stored automatically.

## Data being stored

- Text input in the LOM metadata sections "Lifecycle -> Contributor" and "Meta-Metadata -> Contributor"
- Directly after object creation of surveys and tests the firstname and lastname is stored in the
  LOM-metadata section "Lifecycle"
  
## Data being presented

- All accounts with permission "Edit Settings" for a specific object can read and write all metadata.
- All accounts with copy permission are allowed to access all metadata in the copy. 
  
  
## Data being deleted

- Metadata is deleted after permanent object deletion. Note: the garbage collection is only triggered after any object is deleted from trash 
  or removed permanently (disabled trash functionality).


## Data being exported 

- LOM metadata supporting objects do always export all metadata. 
  
