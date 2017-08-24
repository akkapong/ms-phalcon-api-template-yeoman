<?php

use Phalcon\DI;

use App\Services\CacheService;

class CacheServiceTest extends UnitTestCase
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
    public function testSortParameter()
    {
        //create class
        $class = new CacheService();

        //create params
        $params = [
            [
                'a' => '11',
                'c' => '33',
                'b' => '22',
                'd' => '44',
            ]
        ];

        //call method
        $result = $this->callMethod(
            $class,
            'sortParameter',
            $params
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals([
            'a' => '11',
            'b' => '22',
            'c' => '33',
            'd' => '44',
        ], $result);
    }
    
    public function testEncodeParam()
    {
        //create class
        $class = new CacheService();

        //create params
        $params = [
            [
                'a' => '11',
                'c' => '33',
                'b' => '22',
                'd' => '44',
            ]
        ];

        //call method
        $result = $this->callMethod(
            $class,
            'encodeParam',
            $params
        );

        //check result
        $this->assertInternalType('string', $result);
    }

    public function testGenerateCacheKey()
    {
        $params = [
            'a' => '11',
            'b' => '22',
        ];
        //create class
        $class = $this->getMockBuilder('App\Services\CacheService')
                           ->setMethods([
                                'sortParameter',
                                'encodeParam'
                            ])
                           ->getMock();

        $class->method('sortParameter')
                   ->willReturn($params);

        $class->method('encodeParam')
                   ->willReturn('d8559f62f144f5a870464f7a4f39f39c');

        $result = $class->generateCacheKey('Test', 'App\Controller\TestController::getTestAction', $params);

        //check result
        $this->assertInternalType('string', $result);
        $this->assertEquals("Test-App\Controller\TestController::getTestAction-d8559f62f144f5a870464f7a4f39f39c", $result);
    }

    public function testCheckCache()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('exists')->andReturn(true);

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = new CacheService();

        //call method 
        $result = $class->checkCache("Test-App\Controller\TestController::getTestAction-d8559f62f144f5a870464f7a4f39f39c");

        //check result
        $this->assertTrue($result);
    }

    public function testGetCacheHaveCache()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('get')->andReturn("Test");

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = $this->getMockBuilder('App\Services\CacheService')
                           ->setMethods([
                                'checkCache'
                            ])
                           ->getMock();

        $class->method('checkCache')
                   ->willReturn(true);

        //call method 
        $result = $class->getCache("Test-App\Controller\TestController::getTestAction-d8559f62f144f5a870464f7a4f39f39c");

        //check result
        $this->assertNotNull($result);
        $this->assertEquals("Test", $result);
    }

    public function testGetCacheNotHaveCache()
    {
        //create class
        $class = $this->getMockBuilder('App\Services\CacheService')
                           ->setMethods([
                                'checkCache'
                            ])
                           ->getMock();

        $class->method('checkCache')
                   ->willReturn(false);

        //call method 
        $result = $class->getCache("Test-App\Controller\TestController::getTestAction-d8559f62f144f5a870464f7a4f39f39c");

        //check result
        $this->assertNull($result);
    }

    public function testGetCacheByPrefix()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('queryKeys')->andReturn([
            "prefix-key1"
        ]);

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = $this->getMockBuilder('App\Services\CacheService')
                           ->setMethods([
                                'getCache'
                            ])
                           ->getMock();

        $class->method('getCache')
                   ->willReturn("Test");

        //call method 
        $result = $class->getCacheByPrefix("prefix");

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('prefix-key1', $result);
        $this->assertEquals('Test', $result['prefix-key1']);

    }

    public function testAddCache()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('save')->andReturn(true);

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = new CacheService();

        //call method 
        $result = $class->addCache("key1", "Test1");

        //check result
        $this->assertTrue($result);
    }

    public function testDeleteCache()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('delete')->andReturn(true);

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = new CacheService();

        //call method 
        $result = $class->deleteCache("key1");

        //check result
        $this->assertTrue($result);
    }

    public function testDeleteCacheByPrefix()
    {
        //mock cache
        $cache = Mockery::mock('CACHE');
        $cache->shouldReceive('queryKeys')->andReturn([
            "prefix-key1"
        ]);

        //register
        $this->di->set('cache', $cache, true);

        //create class
        $class = $this->getMockBuilder('App\Services\CacheService')
                           ->setMethods([
                                'deleteCache'
                            ])
                           ->getMock();

        $class->method('deleteCache')
                   ->willReturn(true);

        //call method 
        $result = $class->deleteCacheByPrefix("prefix");

        //check result
        $this->assertTrue($result);

    }
    //------- end: Test function --------//
}