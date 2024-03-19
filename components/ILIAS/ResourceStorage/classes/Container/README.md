Container Resource GUI
======================
The Container Resource GUI is used to administer container resources (see). 
It offers general basic functions for editing files and folders within a 
container resource.

The consuming components also have the option of integrating their own 
rudimentary actions for their own entries (see below).

## Integration into own components via Ctrl-Flow
Via the own components, for example, you can be redirected to the 
`ilContainerResourceGUI` via a tab or link. In doing so, you transfer a 
`Cconfiguration` of which functions should be available:

```php
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        // ...
        switch ($next_class) {
            case strtolower(ilContainerResourceGUI::class):
                $this->tabs->activateTab('id_list_files');
                // Check wite access to determine upload and manage capabilities
                $check_access = $this->access->checkAccess('write', '', $this->object->getRefId());

                // Build the view configuration
                $view_configuration = new Configuration(
                    $container_resource, // the container resource we want to edit
                    new MyStakeholder(),
                    $this->lng->txt('files'), // Title of the Table
                    Mode::DATA_TABLE, // Currently only data table is supported
                    250, // Number of entries per page
                    $check_access,  // Check access to determine upload capabilities
                    $check_access  // Check access to determine manage capabilities
                );

                // build the collection GUI
                $container_gui = new ilContainerResourceGUI(
                    $view_configuration
                );

                // forward the command
                $this->ctrl->forwardCommand($container_gui);
                break;
            case 'ilwhatevergui':
                // ...
```

## Integration of own actions
You can simply add your own actions to the individual entries in the table, 
which must then be processed by a separate method in the GUI class.

```php
                // Add a single action for text-files to set as startfile
                $view_configuration = $view_configuration->withExternalAction(
                    $this->lng->txt('my_command'), // Label
                    self::class, // Target GUI
                    'myCommand', // Target command
                    'ns', // namespace of the parameter (see below)
                    'path', // name of the parameter (see below)
                    false,  // action is available for directories as well (otherwise only for files)
                    ['text/*'] // mime types for which the action is available, wildcard * is allowed
                );
```

At the moment you can only get the selected paths, if you need more options, 
please contact fabian@sr.solutions.

```php
    public function myCommand(): void
    {
        $query = $this->http->wrapper()->query();
        $refinery = $this->refinery;
        // concatenate the namespace and the parameter name from above,
        // this is because UI\Table\Data builds that like this 
        $paths_in_container = $query->has('ns_path') 
            ?  $query->retrieve(
                'ns_path', 
                $refinery->kindlyTo()->listOf(
                    $refinery->kindlyTo()->string()
                )) ?? []
            : [];
            
        // we will always get an array of paths, even if only one is selected
        // so we take the first one
        $path = $paths_in_container[0] ?? '';    
            
        // the ilContainerResourceGUI uses e bin2hex/hex2bin serialization of 
        // pathes. Due to the internals of UI\Table\Data it's not possible 
        // to have a different handling for the parameter in case of external 
        // actions...
        try {
            $path = hex2bin($path);
        } catch (Throwable $e) {
            $path = '';
        }

        // do now whatever you want with the path

        // redirect back to the container resource GUI
        $this->ctrl->redirectByClass(ilContainerResourceGUI::class);
    }
```



