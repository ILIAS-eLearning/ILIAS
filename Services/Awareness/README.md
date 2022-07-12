# Awareness Service

The awareness service basically implements the Who-Is-Online tool.

The tool lists different sets of users (e.g. users sharing the same memberships or system contacts) and their online status.

## Who-Is-Online tool: Implementing a User Provider

The set of users appearing in the who-is-online tool is easily extensible. To add a new set of users to the tool simply provide a class implementing `ILIAS\Awareness\User\Provider` and add the class to `ILIAS\Awareness\User\ProviderFactory`. The second step should be replaced by the artefact reader in the future, see ROADMAP.md.

The class must provide a constructor that accepts the DI Container as only parameter.

The provider ID returned by `getProviderId` should start with your service or module ID to ensure global uniqueness.

The provider ID returned by `getProviderId` should start with your service or module ID to ensure global uniqueness.

The method `getInitialUserSet` must return and int[] array with the user ids of your set. A set of user ids may already be passed to this method in the case the widget should only present user being online for this set. All user IDs being passed are "online users". The method may only return a subset of these IDs or use it to increase performance. However the service ensures that offline users are removed later, too.

## Business Rules Who-Is-Online tool

- The service will ensure that users who have not agreed to be listed in the who-is-online tool will not be listed.
- The service will ensure that users who have not agreed to the terms of services yet will not be listed.