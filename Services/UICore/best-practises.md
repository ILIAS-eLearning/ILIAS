# Introducing new ilCtrl best-practises

This manual explains how ilCtrl should be used in the future to be able to move into a direction where static routes can
become a possibility. It covers how ilCtrl should be used when implementing new features or components, and how current
code could be migrated to meet ilCtrl's new standards.

This guide will most definitely not be able to cover every scenario because there are some very unique use-cases that
cannot be guided by some general best-practises (though it might still be helpful).

## DON'TS (what you shouldn't do)

### `ilCtrl::setCmdClass`

**You MUST STOP using this method, it's been deprecated and will be removed with ILIAS 10.** This method-call can be avoided
in most cases by simply generating absolute ilCtrl-links with `ilCtrl::getLinkTargetByClass` and supplying it with the
complete array of classes from base-class to target-class.

### `ilCtrl::setCmd`

**You MUST STOP using this method, it's been deprecated and will be removed with ILIAS 10.** Normally, `setCmd` can simply
be avoided by passing along the right command from the beginning when link-targets are generated with `ilCtrl::getLinkTargetByClass`. 

### Manipulating `ilCtrl::getCmd`

Apart from manipulating the output of this method with `ilCtrl::setCmd`, you also MUST NOT change the return-value of this
method call in any way. Concatenating substrings to the command after it's retrieved prevents to properly assume which method
will be called in the target class.

### Using `ilCtrl::getLinkTarget` and `ilCtrl::getFormAction`

You SHOULD NOT use these methods anymore, because the more fully-qualified link-targets are built right now the easier becomes
the migration to static routes sometime in the future. Instead of using e.g. `getLinkTarget($this)` you SHOULD use 
`ilCtrl::getLinkTargetByClass([BaseClass, MaybeAnotherGUI, self::class])`.
Not being able to dictate ilCtrl the fully-qualified path indicates a design-flaw that should be restructured anyways.

### Concatenating link-targets without using ilCtrl 

You MUST NOT build link-targets on your own, by concatenating strings to look like an URL returned by ilCtrl. This could
be due to ilCtrl changing it's parameter-names in the future and this would break every link that's been built this way.

### `ilCtrl::getHTML`

You MUST NOT use this method anymore, instead only `ilCtrl::forwardCommand` should be used. This is due the state of ilCtrl
being altered in this method, which leads to the same issues as for `ilCtrl::setCmdClass`.
This method is currently only used for classes like `ilColumnGUI` or `ilBlockGUI`, which also shoudn't be used anymore.
I'm planing on deprecating this method in the future because it shouldn't be a concern of ilCtrl and has nothing to do
with routing.

## DO's (what you should do)

### `ilCtrl::getLinkTargetByClass` and `ilCtrl::getFormActionByClass`

You MUST build all link-targets with these two methods **fully qualified**. You also MUST NOT use anything dynamic when passing
along the classes-array, so DON'T USE methods like `get_class($obj)`. Anything that might be dynamic about this link-target
should be considered as a parameter in the future.

### Command-names that correspond to a method-name.

You MUST only use command-names that correspond to a method-name in the target-class. Therefore, 
`ilCtrl::getLinkTargetByClass([BaseClass, TargetClass], myCommand)` dictate that `TargetClass` must implement a method
named `myCommand`. This allows to possibly migrate ilCtrl-link-targets to routes that are added as PHP-Attributes right
above the corresponding method.
