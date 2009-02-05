<?php
/**
* Smarty PHPunit tests for template_exists methode
* 
* @package PHPunit
* @author Uwe Tews 
*/

require_once '../libs/Smarty.class.php';

/**
* class for template_exists tests
*/
class TemplateExistsTests extends PHPUnit_Framework_TestCase {
    public function setUp()
    {
        $this->smarty = new Smarty();
        $this->smarty->plugins_dir = array('..' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR);
        $this->smarty->enableSecurity();
        $this->old_error_level = error_reporting();
        error_reporting(E_ALL);
    } 

    public function tearDown()
    {
        error_reporting($this->old_error_level);
        unset($this->smarty);
        Smarty::$template_objects = null;
    } 

    /**
    * test $smarty->template_exists true
    */
    public function testSmartyTemplateExists()
    {
        $this->assertTrue($this->smarty->template_exists('helloworld.tpl'));
    } 
    /**
    * test $smarty->template_exists false
    */
    public function testSmartyTemplateNotExists()
    {
        $this->assertFalse($this->smarty->template_exists('notthere.tpl'));
    } 
} 

?>