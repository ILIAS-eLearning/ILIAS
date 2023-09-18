# Style to Repo

These scripts ensure that all style-relevant files are copied to a separate repository.
With each push/merge to the original repository, a commit is created when changes are 
made to the style files, which is then pushed into the style repository.  
This commit contains the original commit message, the commit hash and a URL to the corresponding commit.

## Steps to do

Please change the placeholder values of the variables in deploy.sh.

STYLE_REPO="https://github.com/foo/style_test.git"
STYLE_REPO_NAME_SHORT="foo/style_test.git"  

### Add a token for an user with admin access to style repository

- open settings for the user on github
- click 'Developer settings/Personal access tokens/Tokens (classic)'
- click 'Generate new token (classic)'
- add a note for the token
- check repo
- click 'Generate token'
- copy the generated token for the next step

### Add a secret to the original repository on github

- open 'Settings' for the repo on github
- select 'Secrets/Actions' from left menu
- click on 'New repository secret'
- the name must be 'STYLE_REPO_NAME_SHORT'
- the secret is the token from the step before
- add the username of the user as variable "STYLE_REPO_USER_NAME"
