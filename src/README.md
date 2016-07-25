# Rules for the ./src-Folder and the ILIAS namespace
 
This rules are to be understood according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt).
 
1. The ./src-Directory is the root of the ILIAS-namespace. All directories and files in the
  ./src-Directory MUST comply to PSR-4, where the vendor-namespace is ILIAS.
2. The ILIAS-namespace is meant to contain library-like functionality with clear boundaries
  and purpose. This means:
    1. All code in the ILIAS-namespace MUST provide cross sectional functionality. There
        might be some change in the  later on to port component specific code to the ILIAS
        namespace as well, but for the moment the component specific code resides in the
        Services- and Modules directories. This means that the code for a hypothetical
        general notification system would be going to  ILIAS-namespace, while implementation
        specifics for the notifications of a course
        should go to Modules/Course.
    2. Every subnamespace of ILIAS is considered to represent one library of ILIAS.
3. All libraries MUST expose a clear public interface to their consumers. Every class,
   interface or trait on the top level of the libary is considered to be part of the public
   interface, as well as all entities that are reachable via entities on the top level.
4. Libraries MUST NOT be subject to a breaking change in an ILIAS release-branch.
5. Breaking changes to a library MUST be announced at least one month in advance on the Jour
   Fixe. Breaking changes to a library MUST be made in the trunk.
6. The public interface of the libraries MUST be documented with a Doc-Block. Classes,
   interfaces or traits MUST at least be documented with one sentence telling the purpose of
   the entity. Functions and Methods MUST at least be documented with one sentence giving the
   semantics and the in- and outputs in PHP-Doc-format.
. Libraries SHOULD contain a README.md on the top-level, that explains the purpose and design
   of the library and gives usage examples.
8. Libraries SHOULD contain a CHANGELOG.md on the top-level, that informs about the history of
   the library. The file should enable users of the library to incorporate the changes.
9. Classes, interfaces and traits MUST NOT have an il-Prefix like the classes in Services and
   Modules have.
10. Libraries SHOULD provide automated tests for PHPUnit, where the tests MUST be put in a 
  subdirectory of ./tests, which has the same name as the library.
11. Libraries SHOULD NOT maintain an implicit internal state. All internal states SHOULD be
   made explicit. This means, e.g., that the usage of globals, static variables and the
   Singleton-pattern are prohibited.
12. Libraries SHOULD be parametrized on their IO-operations. That means, e.g., that libraries
   should not use `echo` directly or retrieve a database from a global but instead get
   dependencies like that via injection.
13. Anyone who wants to make additions to the ./src-folder SHOULD discuss them with the
   Technical Board in advance.
14. Library-like code SHOULD be added to the src-Folder and not to the Modules- or Service-
   directory.
 
