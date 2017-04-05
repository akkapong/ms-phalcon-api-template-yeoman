<?php

use Phalcon\DI;

use App\Library\MyLibrary;

class MyLibraryTest extends UnitTestCase
{
    //------ start: MOCK DATA ---------//
    
    //------ end: MOCK DATA ---------//


    //------- start: Method for support test --------//
    protected static function callMethod($obj, $name, array $args) {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
    //------- end: Method for support test --------//

    //------- start: Test function --------//
    public function testAddLangToKeyParams()
    {
        //create class
        $class = new MyLibrary();

        //call method
        $result = $class->addLangToKeyParams(['name' => 'Test', 'des' => 'Description'], ['name']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('name.en', $result);
        $this->assertArrayHasKey('des', $result);
        $this->assertFalse(isset($result['name']));
    }

    public function testAddLangToAllowFilter()
    {
        //create class
        $class = new MyLibrary();

        //call method
        $result = $class->addLangToAllowFilter(['name', 'desc'], ['name']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals(count($result), 2);
        $this->assertTrue(in_array('name.en', $result));
        $this->assertFalse(in_array('name', $result));
        $this->assertTrue(in_array('desc', $result));
    }

    public function testCreateFilterCheckAllow()
    {
        //create class
        $class = new MyLibrary();

        //call method
        $result = $class->createFilterCheckAllow([
            'name' => 'Test', 
            'desc' => 'Description'
        ], ['name']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Test', $result['name']);
        $this->assertFalse(isset($result['desc']));
    }
    //------- end: Test function --------//
}