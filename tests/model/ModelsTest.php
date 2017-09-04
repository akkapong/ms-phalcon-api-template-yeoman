<?php

use Phalcon\DI;
use MongoDB\Driver\Query;
use App\Models\Models;

class ModelsTest extends UnitTestCase
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

    protected function registerClass()
    {
        //mock config
        $config = new \Phalcon\Config( [
            'database' => [
                'mongo' => [
                    'host'     => 'rpp_exchange_mongo',
                    'port'     => '27017',
                    'username' => '',
                    'password' => '',
                    'dbname'   => 'rpp_exchange',
                ],
            ],
        ] ); 

        //register config
        $this->di->set('config', $config, true);

        //mock mongo
        $mongo = Mockery::mock('Mongo');

        //register mongo
        $this->di->set('mongo', $mongo, true);

        //mock mongoService
        $mongoService = Mockery::mock('mongoService');
        $mongoService->shouldReceive('createMongoId')->andReturn('xxxxxxxxxxx');

        //register mongo
        $this->di->set('mongoService', $mongoService, true);
    }
    //------- end: Method for support test --------//

    //------- start: Test function --------//
    public function testFind()
    {
        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeQuery')->andReturn(['0' => 'Test']);

        //register mongo
        $this->di->set('mongo', $mongo, true);

        $filter = [
            'name' => 'Test'
        ];

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn("model");


        $result = $class->find([$filter]);
    
        //check result
        $this->assertEquals('Test', $result[0]);


    }
    
    public function testAssignDataToModel()
    {
        //regiser class
        $this->registerClass();
        //create class
        $class = new Models();
        $class->key1 = '';
        $class->key2 = '';
        //define parameter
        
        $dataObj = new StdClass;
        $dataObj->key1 = 'XXXX';
        $dataObj->key3 = 'YYYY';
        $dataObj->_id = '1';


        $filter = ['name' => 'test'];
        //call method
        $result = $this->callMethod(
            $class,
            'assignDataToModel',
            [$dataObj, $filter]
        );

        //check result
        $this->assertNotNull($result);
        $this->assertInternalType('array', $result->lastQuery);
        $this->assertEquals('XXXX', $result->key1);
    }

    public function testFindByIdNoData()
    {
        //regiser class
        $this->registerClass();


        $filter = [
            'name' => 'Test'
        ];

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['find'])
                    ->getMock();

        $class->method('find')
            ->willReturn([]);


        $result = $class->findById([$filter]);

        //check result
        $this->assertNull($result);
    }

    public function testFindByIdHaveData()
    {
        //regiser class
        $this->registerClass();

        //mock res
        $res = Mockery::mock('Response');

        $filter = [
            'name' => 'Test'
        ];

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['find', 'assignDataToModel'])
                    ->getMock();

        $class->method('find')
            ->willReturn([
                '0' => 'test'
            ]);

        $class->method('assignDataToModel')
            ->willReturn($res);


        $result = $class->findById([$filter]);

        //check result
        $this->assertInternalType('object', $result);
        $this->assertNotNull($result);
    }

    public function testCount()
    {
        $res     = new StdClass;
        $res->n  = 5;
        $coutRes = Mockery::mock('CoutRes');
        $coutRes->shouldReceive('toArray')->andReturn([$res]);
        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeCommand')->andReturn($coutRes);

        //register mongo
        $this->di->set('mongo', $mongo, true);

        $filter = [[
            'name' => 'Test'
        ]];

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn("model");


        $result = $class->count($filter);

        //check result
        $this->assertEquals(5, $result);
    }

    public function testGetOnlyDataNoId()
    {
        //regiser class
        $this->registerClass();

        //create class
        $class = new Models();
        $class->key1 = '111';
        $class->key2 = '222';
        $class->key3 = [
            'sub1' => 's11',
            'sub2' => 's22',
        ];

        $result = $class->getOnlyData();

        //check result
        $this->assertInternalType('object', $result);
        $this->assertEquals('111', $result->key1);
        $this->assertEquals('222', $result->key2);
        $this->assertInternalType('array', $result->key3);
        $this->assertArrayHasKey('sub1', $result->key3);
        $this->assertArrayHasKey('sub2', $result->key3);
    }

    public function testGetOnlyDataId()
    {
        //regiser class
        $this->registerClass();

        //create class
        $class = new Models();
        $class->_id  = 'id';
        $class->key1 = '111';
        $class->key2 = '222';
        $class->lastQuery = 'data';
        $class->key3 = [
            'sub1' => 's11',
            'sub2' => 's22',
        ];

        $result = $class->getOnlyData();

        //check result
        $this->assertInternalType('object', $result);
        $this->assertEquals('id', $result->_id);
        $this->assertEquals('111', $result->key1);
        $this->assertEquals('222', $result->key2);
        $this->assertInternalType('array', $result->key3);
        $this->assertArrayHasKey('sub1', $result->key3);
        $this->assertArrayHasKey('sub2', $result->key3);
    }

    public function testSaveUpdateSuccess()
    {
        //mock datas
        $datas = new StdClass();
        $datas->key1 = '111';
        $datas->key2 = '222';
        $datas->key3 = '333';

        //regiser class
        $this->registerClass();

        $res   = Mockery::mock('RESULT');
        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn($res);

        //register mongo
        $this->di->set('mongo', $mongo, true);


        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource', 'getOnlyData'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');

        $class->method('getOnlyData')
            ->willReturn($datas);

        $class->lastQuery = ['_id' => 'xxxxxxxxx'];

        //call method
        $result = $class->save();

        //check result
        $this->assertTrue($result);
    }

    public function testSaveUpdateFail()
    {
        //mock datas
        $datas = new StdClass();
        $datas->key1 = '111';
        $datas->key2 = '222';
        $datas->key3 = '333';

        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn(null);

        //register mongo
        $this->di->set('mongo', $mongo, true);


        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource', 'getOnlyData'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');

        $class->method('getOnlyData')
            ->willReturn($datas);

        $class->lastQuery = ['_id' => 'xxxxxxxxx'];

        //call method
        $result = $class->save();

        //check result
        $this->assertFalse($result);
    }

    public function testSaveCreateSuccess()
    {
        //mock datas
        $datas = new StdClass();
        $datas->key1 = '111';
        $datas->key2 = '222';
        $datas->key3 = '333';

        //regiser class
        $this->registerClass();

        $res   = Mockery::mock('RESULT');
        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn($res);

        //register mongo
        $this->di->set('mongo', $mongo, true);


        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource', 'getOnlyData'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');

        $class->method('getOnlyData')
            ->willReturn($datas);

        //call method
        $result = $class->save();

        //check result
        $this->assertTrue($result);
    }

    public function testSaveCreateFail()
    {
        //mock datas
        $datas = new StdClass();
        $datas->key1 = '111';
        $datas->key2 = '222';
        $datas->key3 = '333';

        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn(null);

        //register mongo
        $this->di->set('mongo', $mongo, true);


        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource', 'getOnlyData'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');

        $class->method('getOnlyData')
            ->willReturn($datas);

        //call method
        $result = $class->save();

        //check result
        $this->assertFalse($result);
    }

    public function testDeleteNoLastQuery()
    {
        //regiser class
        $this->registerClass();

        $class = new Models();

        //call method
        $result = $class->delete();

        //check result
        $this->assertFalse($result);
    }

    public function testDeleteSuccess()
    {
        //regiser class
        $this->registerClass();

        $res   = Mockery::mock('RESULT');
        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn($res);

        //register mongo
        $this->di->set('mongo', $mongo, true);

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');

        $class->lastQuery = ['_id' => 'xxxxxxx'];

        //call method
        $result = $class->delete();

        //check result
        $this->assertTrue($result);
    }

    public function testDeleteFail()
    {
        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeBulkWrite')->andReturn(null);

        //register mongo
        $this->di->set('mongo', $mongo, true);

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn('model');
            
        $class->lastQuery = ['_id' => 'xxxxxxx'];

        //call method
        $result = $class->delete();

        //check result
        $this->assertFalse($result);
    }

    public function testAggregate()
    {
        $res     = new StdClass;
        $res->_id  = 'test';
        $res->sum  = 5;

        $coutRes = [$res];
        //regiser class
        $this->registerClass();

        //mock mongo
        $mongo = Mockery::mock('Mongo');
        $mongo->shouldReceive('executeCommand')->andReturn($coutRes);

        //register mongo
        $this->di->set('mongo', $mongo, true);

        $filter = [[
            '$group' => [
                '_id' => '$key',
                'sum' => ['$sum' => 1],
            ]
        ]];

        //create class
        $class = $this->getMockBuilder('App\Models\Models')
                    ->setMethods(['getSource'])
                    ->getMock();

        $class->method('getSource')
            ->willReturn("model");


        $result = $class->aggregate($filter);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertInternalType('object', $result[0]);
        $this->assertEquals('test', $result[0]->_id);
        $this->assertEquals(5, $result[0]->sum);

    }
    //------- end: Test function --------//
}
