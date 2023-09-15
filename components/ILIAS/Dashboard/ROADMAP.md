# Dashboard

## Short Term

### Remove "Personal Desktop"

- Final naming cleanup (remove all "PD" names).

## Mid Term

### Move to global screen concepts

Dashboard blocks should be organised similar to menu items to meet the requirements of the [TB](https://docu.ilias.de/goto_docu_file_8005_download.html)

In detail a component should provide all the necessary information on its presentation and required settings to the `Dashboard` by implementing a `Provider Interface`. The providers must then be collected by the `Dashboard` dynamicly. This change should ensure that the `Dashboard` has no knowledge about the blocks wich are provided to its presentation beside their offered settings.
In response to that improvement all static references to the exsisting `Dashboard` blocks ...

- Favoruites/Selected Items
- Recommended Content
- Course and Groups
- Learning Sequences
- Study Programme

... should be removed from the `Dashboard` itsself.