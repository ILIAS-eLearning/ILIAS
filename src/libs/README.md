# Rules for the ILIAS\libs namespace and the ./src/libs-Folder
 
This rules are to be understood according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt).
 
1. The ILIAS\libs-namespace is meant to contain library-like functionality with clear
   boundaries and purpose. This means:
    1. All code in the ILIAS\libs-namespace MUST provide cross sectional functionality.
       This means that the code for a hypothetical general notification system would 
       be going to the ILIAS\libs-namespace, while implementation specifics for the 
       notifications of a course should go to ILIAS\Modules\Course.
    2. Every subnamespace of ILIAS\libs is considered to represent one library of ILIAS.
2. The ./src/libs-Directory is the root of the ILIAS\libs-namespace. All directories and
   files in the ./src/libs-Directory MUST comply to PSR-4, where the vendor-namespace
   is ILIAS.
3. Library-like code SHOULD be added to the src/libs-Folder and not to the Modules- or
   Service-directory.
4. Anyone who wants to make additions to the ./src/libs-folder SHOULD discuss them with
   the Technical Board in advance.
5. Libraries SHOULD contain a README.md on the top-level, that explains the purpose
   and design of the library and gives usage examples.
6. All libraries MUST expose a clear public interface to their consumers. Every
   class, interface or trait on the top level of the library is considered to be a
   part of the public interface, as well as all entities that are reachable via
   entities on the top level.
7. The public interface of the libraries MUST be documented with Doc-Blocks.
   Classes, interfaces or traits MUST at least be documented with one sentence
   telling the purpose of the entity. Functions and Methods MUST at least be 
   documented with one sentence giving the semantics and the in- and outputs in
   PHP-Doc-format.
8. Libraries MUST NOT be subject to a breaking change in an ILIAS release-branch.
9. Breaking changes to a library MUST be announced at least one month in advance
   on the Jour Fixe. Breaking changes to a library MUST be made in the trunk.
10. Libraries SHOULD contain a CHANGELOG.md on the top-level, that informs about
   the history of the library. The file should enable users of the library to
   incorporate the changes.
11. Libraries SHOULD provide automated tests for PHPUnit, where the tests MUST be
   put in a subdirectory of ./tests, which has the same name as the library.
12. Libraries SHOULD be parametrized on their IO-operations. That means, e.g., that
  libraries should not use `echo` directly or retrieve a database from a global but
  instead get dependencies like that via injection.
13. Libraries SHOULD NOT maintain an implicit internal state. All internal states
   SHOULD be made explicit. This means, e.g., that the usage of globals, static
   variables and the Singleton-pattern are prohibited.
 
