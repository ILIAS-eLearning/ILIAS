# UI Components

## What is This About?
The UI-Frameworks consists of a set of components along with a semantic description of each component including a set of 
guidelines of how to use them.

UI Components serve a specific purpose. They are not simply named html structures that are composed to larger structures, 
but semantically different identities. It is possible that two different component look the same and act the same by 
accident, but still remain different identities. However it is also possible that the same component, looks different 
in seperate contexts.


## Why do I Need This in ILIAS?

The ILIAS UI-Framework helps you to implement GUIs consistently. You won't need to think about HTML if you're using this 
framework. You also won't need to think about the implementation you are using, the device your GUI is displayed on or the 
CSS-classes you need to use. You will be able to talk to other people (like users or designers) using the same concepts 
and problem space as they do. Note, that this is also not a templating framework.

## How to Proceed?


The factories provided by the framework provide access to all Components available. The main factory provides methods for 
every node or leaf in the Class-Layer of the Kitchen Sink Taxonomy. Using that method you get a sub factory if methods 
corresponds to a node in the layout. If the method corresponds to a leaf in the layout, you get a PHP representation 
of the component you chose.

The entries of the Kitchen Sink are documented in this framework in a machine readable form. That means you can rely 
on the documentation given in the interfaces to the factories, other representations of the Kitchen Sink are derived 
from there. This also means you can chose to use the documentation of the Kitchen Sink in ILIAS
to check out the components. Note that this documentation can be found in every installation in: Administration -> Layout
and Navigation -> Layout -> Delos -> Documentation (Action).

With the ILIAS UI-Framework you describe how your GUI is structured instead of instructing the system to construct it for 
you. The main principle for the description of GUIs is composition.

You declare you components by providing a minimum set of properties and maybe other components that are bundled in your 
component. All compents in the framework strive to only use a small amount of required properties and provide sensible 
defaults for other properties.

Since the representation of the components are implemented as immutable objects, you can savely reuse components created 
elsewhere in your code, or pass your component to other code without being concerned if the other code modifies it.

You find a vast array of examples on the documentation page of the UI Components (to check out the components. Note that 
this documentation can be found in every installation in: Administration -> Layout and Navigation -> Layout -> Delos -> 
Documentation) or directly in the Repo [Examples](../../../components/ILIAS/UI/src/examples).

## What do I Need to Watch Out For? (Dos & Dont's)

- Do report issues with the Doc, this Tutorial or the Components if you find any directly in mantis. Best provide a 
PR to resolve the issue if possible.
- Use the components regarding the guidelines and semantics for all your output.
- Do NOT write HTML or CSS for your components, use the UI Components to render things.
- Only use the Legacy Component to nest things if you are absolutely sure, that this is the only possible solution.
- See the tutorial for Adding or Changing Components if you need anything not yet available. Do actively collaborate 
and participate in it’s development.