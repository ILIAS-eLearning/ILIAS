# Roadmap

## Short Term

...

## Mid Term

### Listen for changes of target object's title

ContainerReference objects that reuse the title of their target should update their title
when the target does, e.g. via event handling. Currently, different components
handle the rendering of the title of references differently, some looking up
the title of the target and some just using the title of the reference directly from
object_data, leading to inconsistent results when the title of the target is
changed.

## Long Term

...