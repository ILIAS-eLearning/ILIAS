<?php
/**
 * TestCase for the ilContext
 * @group needsInstalledILIAS
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilInitialisationTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        PHPUnit_Framework_Error_Deprecated::$enabled = false;

        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }
    
    /**
    * @dataProvider globalsProvider
    */
    public function test_DIC($global_name, $class_name)
    {
        global $DIC;

        $this->assertInstanceOf($class_name, $GLOBALS[$global_name]);
        $this->assertInstanceOf($class_name, $DIC[$global_name]);
        $this->assertSame($GLOBALS[$global_name], $DIC[$global_name]);
    }

    /**
     * @dataProvider getterProvider
     */
    public function test_DIC_getters($class_name, $getter)
    {
        global $DIC;

        $service = $getter($DIC);
        $this->assertInstanceOf($class_name, $service);
    }

    public function globalsProvider()
    {
        // Add combinations of globals and their classes here...
        return array( array("ilIliasIniFile", "ilIniFile")
            , array("ilCtrl", "ilCtrl")
            , array("tree", "ilTree")
            , array("ilLog", "ilLogger")
            , array("ilDB", "ilDBInterface")
            );
    }

    public function getterProvider()
    {
        return array( array("ilDBInterface", function ($DIC) {
            return $DIC->database();
        })
            , array("ilCtrl", function ($DIC) {
                return $DIC->ctrl();
            })
            , array("ilObjUser", function ($DIC) {
                return $DIC->user();
            })
            , array("ilRbacSystem", function ($DIC) {
                return $DIC->rbac()->system();
            })
            , array("ilRbacAdmin", function ($DIC) {
                return $DIC->rbac()->admin();
            })
            , array("ilRbacReview", function ($DIC) {
                return $DIC->rbac()->review();
            })
            , array("ilAccess", function ($DIC) {
                return $DIC->access();
            })
            , array("ilTree", function ($DIC) {
                return $DIC->repositoryTree();
            })
            , array("ilLanguage", function ($DIC) {
                return $DIC->language();
            })
            // TODO: Can't test these until context for unit tests does not have HTML.
            //, array("ilTemplate", function ($DIC) { return $DIC->ui()->mainTemplate(); })
            //, array("ilToolbarGUI", function ($DIC) { return $DIC->toolbar(); })
            //, array("ilTabsGUI", function ($DIC) { return $DIC->tabs(); })
            //, array("ILIAS\\UI\\Factory", function ($DIC) { return $DIC->ui()->factory();})
            //, array("ILIAS\\UI\\Renderer", function ($DIC) { return $DIC->ui()->renderer();})
            , array("ilLogger", function ($DIC) {
                return $DIC->logger()->root();
            })
            , array("ilLogger", function ($DIC) {
                return $DIC->logger()->grp();
            })
            , array("ilLogger", function ($DIC) {
                return $DIC->logger()->crs();
            })
            , array("ilLogger", function ($DIC) {
                return $DIC->logger()->tree();
            })
            );
    }
}
