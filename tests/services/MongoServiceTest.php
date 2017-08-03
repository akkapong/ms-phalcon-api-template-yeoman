<?php

use Phalcon\DI;

use App\Services\MongoService;

class MongoServiceTest extends UnitTestCase
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
    public function testFormatOutputNoModel()
    {
        //Mock model
        $model = null;
        //create class
        $class = new MongoService();

        //call function
        $result = $class->formatOutput($model);

        //check result
        $this->assertEmpty($result);
    }

    public function testFormatOutputHaveModel()
    {
        //Mock model
        $model = Mockery::mock('Model');
        $model->shouldReceive('toArray')->andReturn([
            'id'   => '58abd2f22f8331000a3acb91',
            'name' => 'Test',
            '_id'  => ['id' => '58abd2f22f8331000a3acb91']
        ]);
        $model->_id = '58abd2f22f8331000a3acb91';

        //create class
        $class = new MongoService();

        //call function
        $result = $class->formatOutput($model);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertFalse(isset($result['_id']));
    } 

    public function testReplaceSpecialKeyOfRegex()
    {
        //create class
        $class = new MongoService();

        //create parameter
        $params = ['ss$dd)gg^'];

        //call method
        $result = $this->callMethod(
            $class,
            'replaceSpecialKeyOfRegex',
            $params
        );
        
        //check result
        $this->assertEquals('ss\\$dd\\)gg\\^', $result);
    }

    public function testConvertValueForSearchLikeNoPercent()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("Test");

        //create parameter
        $params = ["Test"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertFalse($result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtLast()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("Te%");

        //create parameter
        $params = ["Te%"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('^Te', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtFirst()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("%est");


        //create parameter
        $params = ["%est"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('est$', $result[1]);
    }

    public function testConvertValueForSearchLikePercentAtFirstAndLast()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['replaceSpecialKeyOfRegex'])
                    ->getMock();

        $class->method('replaceSpecialKeyOfRegex')
            ->willReturn("%es%");

        //create parameter
        $params = ["%es%"];

        //call method
        $result = $this->callMethod(
            $class,
            'convertValueForSearchLike',
            $params
        );

        //check result
        $this->assertTrue($result[0]);
        $this->assertEquals('es', $result[1]);
    }

    public function testManageFilterValueHaveLike()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([true, '^Tes']);

        //call method
        $result = $class->manageFilterValue('key', 'Tes%', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('$regex', $result['key']);
        $this->assertEquals('^Tes', $result['key']['$regex']);
    }

    public function testManageFilterValueNotLikeNullValue()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([false, 'null']);

        //call method
        $result = $class->manageFilterValue('key', 'null', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertNull($result['key']);
    }

    public function testManageFilterValueNotLikeNotNullValue()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['convertValueForSearchLike'])
                    ->getMock();

        $class->method('convertValueForSearchLike')
            ->willReturn([false, 'Test']);

        //call method
        $result = $class->manageFilterValue('key', 'Test', []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertEquals('Test', $result['key']);
    }

    public function testCreateConditionFilter()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['manageFilterValue', 'manangeBetweenCondition'])
                    ->getMock();

        $class->method('manageFilterValue')
            ->willReturn(['key1' => 'Test1']);

        $class->method('manangeBetweenCondition')
            ->willReturn([
                'key1' => 'Test1',
                'key3' => [
                    '$in' => ['Test3']
                ],
                'key4' => [
                    '$gte' => 1,
                    '$lte' => 5,
                ]
            ]);

        //call method
        $result = $class->createConditionFilter(['key1' => 'Test1', 'key2' => 'Test2', 'key3' => ['Test3'], 'key4' => [1,5]], ['key1', 'key3', 'key4'], ['key3' => '$in', 'key4' => ['$gte', '$lte']]);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertEquals('Test1', $result['key1']);
        $this->assertFalse(isset($result['ke2']));
        $this->assertArrayHasKey('key3', $result);
        $this->assertArrayHasKey('$in', $result['key3']);
        $this->assertInternalType('array', $result['key3']['$in']);
        $this->assertEquals(['Test3'], $result['key3']['$in']);
    }

    public function testManageLimitOffsetInParams()
    {
        //create class
        $class = new MongoService();

        //call method
        $result = $class->manageLimitOffsetInParams(['limit' => 5, 'offset' => 0], []);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals(5, $result['limit']);
        $this->assertArrayHasKey('skip', $result);
        $this->assertEquals(0, $result['skip']);
    }

    public function testGetAllIdFromDatasNoDataFormatString()
    {
        //create class
        $class = new MongoService();

        //call method
        $result = $class->getAllIdFromDatas(null);

        //check result
        $this->assertInternalType('string', $result);
        $this->assertEmpty($result);
    }

    public function testGetAllIdFromDatasNoDataFormatNotString()
    {
        //create class
        $class = new MongoService();

        //call method
        $result = $class->getAllIdFromDatas(null, 'array');

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    public function testGetAllIdFromDatasHaveDataFormatString()
    {
        //Mock data
        $data = Mockery::mock('Data');
        $data->_id = "58abd2f22f8331000a3acb91";
        //create class
        $class = new MongoService();

        //call method
        $result = $class->getAllIdFromDatas([$data]);

        //check result
        $this->assertInternalType('string', $result);
        $this->assertEquals('58abd2f22f8331000a3acb91', $result);
    }

    public function testGetAllIdFromDatasHaveDataFormatNotString()
    {
        //Mock data
        $data = Mockery::mock('Data');
        $data->_id = "58abd2f22f8331000a3acb91";
        //create class
        $class = new MongoService();

        //call method
        $result = $class->getAllIdFromDatas([$data], 'array');

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals('58abd2f22f8331000a3acb91', $result[0]);
    }

    public function testAddIdTodataNotMulti()
    {
        //Mock data object
        $dataObj = Mockery::mock("DataObj");

        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['formatOutput'])
                    ->getMock();

        $class->method('formatOutput')
            ->willReturn([
                'id'   => '58abd2f22f8331000a3acb91', 
                'name' => 'Test1'
            ]);

        //call method
        $result = $class->addIdTodata($dataObj, false);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
    }

    public function testAddIdTodataMulti()
    {
        //Mock data object
        $dataObj = Mockery::mock("DataObj");

        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['formatOutput'])
                    ->getMock();

        $class->method('formatOutput')
            ->willReturn([
                'id'   => '58abd2f22f8331000a3acb91', 
                'name' => 'Test1'
            ]);

        //call method
        $result = $class->addIdTodata([$dataObj]);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function testGetDetailDataById()
    {
        //Mock model 
        $model = Mockery::mock('Model');
        $model->shouldReceive('find')->andReturn([[
                'id'   => '58abd2f22f8331000a3acb91',
                'name' => 'Test'
            ]]);

        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['createConditionFilter'])
                    ->getMock();

        $class->method('createConditionFilter')
            ->willReturn([
                'id' => [
                    '$in' => ['58abd2f22f8331000a3acb91']
                ]
            ]);

        //call method
        $result = $class->getDetailDataById($model, '58abd2f22f8331000a3acb91', ['id']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function testConvertOrderTypeOther()
    {
        //create class
        $class = new MongoService();

        //create parameter
        $params = ['asc'];

        //call method
        $result = $this->callMethod(
            $class,
            'convertOrderType',
            $params
        );

        //check result
        $this->assertEquals(1, $result);
    }

    public function testConvertOrderTypeDesc()
    {
        //create class
        $class = new MongoService();

        //create parameter
        $params = ['desc'];

        //call method
        $result = $this->callMethod(
            $class,
            'convertOrderType',
            $params
        );

        //check result
        $this->assertEquals(-1, $result);
    }

    public function testManageSortDataByIdList()
    {
        //create class
        $class = new MongoService();

        //call method
        $result = $class->manageSortDataByIdList([
            [
                'id'   => '111',
                'name' => 'Test 1'
            ],[
                'id'   => '222',
                'name' => 'Test 2'
            ],[
                'id'   => '333',
                'name' => 'Test 3'
            ]
        ], '222,111,333');

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertEquals('222', $result[0]['id']);
        $this->assertArrayHasKey('id', $result[1]);
        $this->assertEquals('111', $result[1]['id']);
        $this->assertArrayHasKey('id', $result[2]);
        $this->assertEquals('333', $result[2]['id']);
    }
    
    public function testManageOrderInParamsNoOrder()
    {
        //create class
        $class = new MongoService();

        //call method
        $result = $class->manageOrderInParams([], [], []);

        //check result
        $this->assertEmpty($result);
    }

    public function testManageOrderInParamsHaveOrderNotAllowAll()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['convertOrderType'])
                    ->getMock();

        $class->method('convertOrderType')
            ->willReturn(1);

        //call method
        $result = $class->manageOrderInParams([
            'order_by' => 'name:asc,description,name1:desc'
        ], [], ['xxx']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertFalse(isset($result['sort']));
    }

    public function testManageOrderInParamsHaveOrder()
    {
        //creste class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['convertOrderType'])
                    ->getMock();

        $class->method('convertOrderType')
            ->willReturn(1);

        //call method
        $result = $class->manageOrderInParams([
            'order_by' => 'name:asc,description,name1:desc'
        ], [], ['name', 'description']);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('sort', $result);
        $this->assertEquals(1, count($result['sort']));
        $this->assertArrayHasKey('name', $result['sort']);
        $this->assertEquals(1, $result['sort']['name']);
    }

    public function testManageBetweenFilterNoKey()
    {
        //create class
        $class = new MongoService();

        $params  = [
            'date_start' => '2017-01-01', 
            'date_end'   => '2017-05-01',
        ];
        $options = [];
        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($params, $result);
    }

    public function testManageBetweenFilterHaveKeyDateWrong()
    {
        //create class
        $class = new MongoService();

        $params  = [
            'date_start'  => '2017-01-01', 
            'between_key' => 'date',
        ];
        $options = [];

        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($params, $result);
    }

    public function testManageBetweenFilterHaveKeySuccess()
    {
        //create class
        $class   = new MongoService();
        
        $params  = [
            'date_start'  => '2017-01-01', 
            'date_end'    => '2017-05-01',
            'between_key' => 'date',
        ];
        $options = [];

        //call method
        $result = $class->manageBetweenFilter($params, $options);

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayNotHasKey('date_start', $result);
        $this->assertArrayNotHasKey('date_end', $result);
        $this->assertInternalType('array', $result['date']);
        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('date', $options);
    }

    public function testManageId()
    {
        //create class
        $class = new MongoService();

        //create parameter
        $params = ['58abd2f22f8331000a3acb91,58abd2f22f8331000a3acb82'];

        //call method
        $result = $this->callMethod(
            $class,
            'manageId',
            $params
        );
        
        //check result
        $this->assertCount(2, $result);
    }

    public function testGetDetailDataByIdLargeData()
    {
        //mock config
        $config = new \Phalcon\Config([
            'database' => [
                'mongo' => [
                    'host'     => '192.168.200.69',
                    'port'     => '27017',
                    'username' => 'rpp_mtransaction',
                    'password' => '1qaz2wsx',
                    'dbname'   => 'rpp_mtransaction',
                ],
            ]        
        ]);
        //register model
        $this->di->set('config', $config, true);

        $data = new StdClass;
        $data->_id = '58abd2f22f8331000a3acb91';
        $data->key1 = 'k1';
        $data->key2 = 'k2';

        //mock mongoOrig
        $mongoOrig = Mockery::mock('MONGOORIGIN');
        $mongoOrig->shouldReceive('executeQuery')->andReturn([
            $data
        ]);
        //register model
        $this->di->set('mongoOrig', $mongoOrig, true);

        //create class
        $class = $this->getMockBuilder('App\Services\MongoService')
                    ->setMethods(['manageId'])
                    ->getMock();

        $class->method('manageId')
            ->willReturn([
                new \MongoDB\BSON\ObjectID('58abd2f22f8331000a3acb91'),
                new \MongoDB\BSON\ObjectID('58abd2f22f8331000a3acb82'),
            ]);

        $collection = Mockery::mock('COLLECTION');

        //call method
        $result = $class->getDetailDataByIdLargeData($collection, '58abd2f22f8331000a3acb91');

        //check result
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertInternalType('array', $result[0]);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('key1', $result[0]);
        $this->assertArrayHasKey('key2', $result[0]);
    }
    //------- end: Test function --------//
}