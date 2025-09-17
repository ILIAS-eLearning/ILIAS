# Media Pool

## Business Rules

- The **write** permission allows to edit the settings and the content.
- To **re-use** a media pool in another repository object (e.g. a learning module), the **write** permission to the pool is needed. This is due to the fact, that re-use means creating a new reference to the media object and all references currently give the full edit access to a media object, see [README.md of Media Object service](../../components/ILIAS/MediaObjects/README.md)
- Content snippets do not support internal links currently.
- The embedding context will determine the content style being used. If you want to apply the same classes, you must either use the same content style or the same class names.
