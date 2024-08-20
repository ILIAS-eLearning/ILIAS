# Roadmap

## Long Term

### Reduce endpoints located in the ilias root directory

In the future the amount of endpoints (.php classes) located in the ILIAS root directory   
should be reduced and the existing code moved to the existing ilCtrl structure.  
Just the ``index.php`` as the only endpoint may remain.

(With possible exceptions for things like **sso** or **cli**)

Examples: 
- login.php
- logout.php
- error.php
- register.php
- ...

Current Problems:
- Current concepts like the redirecting to ``login.php?cmd=force_login`` have to be adjusted   
  to no longer use the ilCtrl->getCmd() method and additionally check if the cmd is set to ``force_login`` in the POST var.