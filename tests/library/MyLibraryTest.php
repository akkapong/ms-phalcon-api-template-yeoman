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

    public function testObjectToArray()
    {
        //create object
        $obj2 = new StdClass;
        $obj2->sub1 = ['k2s1'];
        $obj2->sub2 = 'k2s2';
        $obj3 = new StdClass;
        $obj3->sub1 = 'k3s2ss1';
        $obj = new StdClass;
        $obj->key1 = 'k1';
        $obj->key2 = $obj2;
        $obj->key3 = [
            'sub1' => 'k3s1',
            'sub2' => $obj3,
        ];
        //create class
        $class = new MyLibrary();

        //call method
        $result = $class->objectToArray($obj);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key2', $result);
        $this->assertArrayHasKey('key3', $result);
        $this->assertEquals('k1', $result['key1']);
        $this->assertInternalType('array', $result['key2']);
        $this->assertArrayHasKey('sub1', $result['key2']);
        $this->assertArrayHasKey('sub2', $result['key2']);
        $this->assertInternalType('array', $result['key2']['sub1']);
        $this->assertEquals('k2s2', $result['key2']['sub2']);
        $this->assertInternalType('array', $result['key3']);
        $this->assertArrayHasKey('sub1', $result['key3']);
        $this->assertArrayHasKey('sub2', $result['key3']);
        $this->assertEquals('k3s1', $result['key3']['sub1']);
        $this->assertInternalType('array', $result['key3']['sub2']);
        $this->assertArrayHasKey('sub1', $result['key3']['sub2']);
        $this->assertEquals('k3s2ss1', $result['key3']['sub2']['sub1']);
    }
    //------- end: Test function --------//
}