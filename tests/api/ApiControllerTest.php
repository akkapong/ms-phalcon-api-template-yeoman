<?php

use App\Controllers\ApiController;
use Phalcon\DI;

class ApiControllerTest extends UnitTestCase
{
    //------ start: MOCK DATA ---------//
    //------ end: MOCK DATA ---------//

    //------- start: Method for support test --------//
    protected static function callMethod($obj, $name, array $args)
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
    //------- end: Method for support test --------//

    //------- start: Test function ---------//
    public function testValidateApiError()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //Mock error
        $error = Mockery::mock('Error');
        $error->shouldReceive("getMessage")->andReturn("Some Error");
        $error->shouldReceive("getField")->andReturn("Field Error");

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['validate'])
                    ->getMock();

        $api->method('validate')
            ->willReturn([
                'error' => $error,
            ]);

        $result = $this->callMethod(
            $api,
            'validateApi',
            [$rules, [], $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['msgError'], "Some Error");
        $this->assertEquals($result['fieldError'], "Field Error");
    }

    public function testValidateApiSuccessNoDefault()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //Mock error
        $error = Mockery::mock('Error');
        $error->shouldReceive("getMessage")->andReturn("Some Error");
        $error->shouldReceive("getField")->andReturn("Field Error");

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['validate'])
                    ->getMock();

        $api->method('validate')
            ->willReturn([]);

        $result = $this->callMethod(
            $api,
            'validateApi',
            [$rules, [], $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $input);
    }

    public function testValidateApiSuccessHaveDefault()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];

        $default = [
            'test1' => '44444',
            'test3' => '33333',
        ];

        $input = [
            'test1' => "11111",
            'test2' => "22222",
        ];

        //Mock error
        $error = Mockery::mock('Error');
        $error->shouldReceive("getMessage")->andReturn("Some Error");
        $error->shouldReceive("getField")->andReturn("Field Error");

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['validate'])
                    ->getMock();

        $api->method('validate')
            ->willReturn([]);

        $result = $this->callMethod(
            $api,
            'validateApi',
            [$rules, $default, $input]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals(count($result), 3);
        $this->assertEquals($result['test1'], '11111');
        $this->assertEquals($result['test3'], '33333');
    }

    public function testValidateNoRequired()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1', 'test2'],
            ],
        ];
        $input = [
            'test1' => "11111",
            'test3' => "33333",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['error'], 'The test2 is required');
    }

    public function testValidateNoNumber()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'number',
                'fields' => ['test1', 'test2'],
            ],
        ];
        $input = [
            'test1' => "11111",
            'test3' => "xxxxx",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['error'], 'Test2 must be numberic');
    }

    public function testValidateNoType()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'test',
                'fields' => ['test1'],
            ],
        ];
        $input = [
            'test1' => "11111",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testValidateNotInWithin()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'within',
                'fields' => ['test1' => ['111', '222']],
            ],
        ];
        $input = [
            'test1' => "11111",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['error'], 'The test1 must be in 111 , 222');
    }

    public function testValidateInWithin()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'within',
                'fields' => ['test1' => ['111', '222']],
            ],
        ];
        $input = [
            'test1' => "111",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testValidateSuccess()
    {
        //Define parameter
        $rules = [
            [
                'type'   => 'required',
                'fields' => ['test1'],
            ], [
                'type'   => 'accesstype',
                'fields' => ['test1'],
            ],
        ];
        $input = [
            'test1' => "MOBILE",
        ];

        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'validate',
            [$input, $rules]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testValidateErrorNoStatusCode()
    {
        //Define parameter
        $fieldError = '';
        $msgError   = '';
        $statusCode = null;

        //mock message
        $status = new \Phalcon\Config([
            'code200' => 200,
            'text200' => 'Success',

            'code400' => 400,
            'text400' => 'Bad Request',
        ]);

        //register
        $this->di->set('status', $status, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['responseData'])
                    ->getMock();

        $api->method('responseData')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'validateError',
            [$fieldError, $msgError, $statusCode]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testValidateErrorHaveStatusCode()
    {
        //Define parameter
        $fieldError = '';
        $msgError   = '';
        $statusCode = 200;

        //mock message
        $status = new \Phalcon\Config([
            'code200' => 200,
            'text200' => 'Success',

            'code400' => 400,
            'text400' => 'Bad Request',
        ]);

        //register
        $this->di->set('status', $status, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['responseData'])
                    ->getMock();

        $api->method('responseData')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'validateError',
            [$fieldError, $msgError, $statusCode]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testValidateErrorHaveStatusCodeAndHaveFieldErr()
    {
        //Define parameter
        $fieldError = 'noData';
        $msgError   = 'No Data';
        $statusCode = 200;

        //mock message
        $status = new \Phalcon\Config([
            'code200' => 200,
            'text200' => 'Success',

            'code400' => 400,
            'text400' => 'Bad Request',
        ]);

        //register
        $this->di->set('status', $status, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['responseData'])
                    ->getMock();

        $api->method('responseData')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'validateError',
            [$fieldError, $msgError, $statusCode]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testValidateBussinessErrorHaveErrorMsg()
    {
        //Define parameter
        $field = 'dataNotFound';

        //mock message
        $message = new \Phalcon\Config([
            'dataNotFound' => [
                'code'     => 400,
                'msgError' => 'Data Not Found',
            ],
        ]);

        //register
        $this->di->set('message', $message, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['validateError'])
                    ->getMock();

        $api->method('validateError')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'validateBussinessError',
            [$field]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testOutputWitTotal()
    {
        //mock message
        $status = new \Phalcon\Config([
            'code200' => 200,
            'text200' => 'Success',

            'code400' => 400,
            'text400' => 'Bad Request',
        ]);

        //register
        $this->di->set('status', $status, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['responseData'])
                    ->getMock();

        $api->method('responseData')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'output',
            ["test", ['limit' => 10, 'offset' => 0, 'totalRecord' => 2]]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testOutput()
    {
        //mock message
        $status = new \Phalcon\Config([
            'code200' => 200,
            'text200' => 'Success',

            'code400' => 400,
            'text400' => 'Bad Request',
        ]);

        //register
        $this->di->set('status', $status, true);

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods(['responseData'])
                    ->getMock();

        $api->method('responseData')
            ->willReturn("MOCK RESULT");

        $result = $this->callMethod(
            $api,
            'output',
            ["test"]
        );

        //check result
        $this->assertEquals($result, "MOCK RESULT");
    }

    public function testResponseData()
    {
        //Mock response
        $response = Mockery::mock('Response');
        $response->shouldReceive("setContentType")->andReturn(true);
        $response->shouldReceive("setStatusCode")->andReturn(true);
        $response->shouldReceive("setJsonContent")->andReturn(true);

        //register
        $this->di->set('response', $response, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'responseData',
            [[], 200, 'success']
        );

        //check result
        $this->assertEquals($result, $response);
    }

    public function testGetPostInput()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("getRawBody")->andReturn('{"test" : "11111"}');

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'getPostInput',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('test', $result);
        $this->assertEquals($result['test'], '11111');
    }

    public function testGetPostInputNoData()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("getRawBody")->andReturn('');

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'getPostInput',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, []);
    }

    public function testGetUrlParam()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("get")->with('test1')->andReturn('11111');
        $request->shouldReceive("get")->with('test2')->andReturn('22222');

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'getUrlParam',
            [['test1', 'test2']]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['test1'], '11111');
        $this->assertEquals($result['test2'], '22222');
    }

    public function testGetAllUrlParam()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive("get")->andReturn([
            'test1' => '11111',
            'test2' => '22222',
            '_url'  => 'http://www.test.com/',
        ]);

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'getAllUrlParam',
            []
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['test1'], '11111');
        $this->assertEquals($result['test2'], '22222');
    }

    public function testResponse()
    {
        $field = [
            'invalidTerminalID' => true,
        ];

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods([
                        'validateTerminalID',
                    ])
                    ->getMock();

        $api->method('validateTerminalID')
            ->willReturn(true);

        $result = $this->callMethod(
            $api,
            'response',
            [$field]
        );

        $this->assertEmpty($result);
    }

    public function testResponseError()
    {
        $field = [
            'invalidTerminalID' => false,
        ];

        //create class
        $api = $this->getMockBuilder('App\Controllers\ApiController')
                    ->setMethods([
                        'validateTerminalID',
                    ])
                    ->getMock();

        $api->method('validateTerminalID')
            ->willReturn(false);

        $result = $this->callMethod(
            $api,
            'response',
            [$field]
        );

        //check result
        $this->assertInternalType('array', $result);
        $this->assertEquals($result['error'][0], 'invalidTerminalID');
    }

    public function testGetLanguageFromHeaderNoLang()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive('getHeaders')->andReturn([]);

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();

        $result = $this->callMethod(
            $api,
            'getLanguageFromHeader',
            []
        );

        //check result
        $this->assertEquals('en', $result);
    }

    public function testGetLanguageFromHeaderHaveLang()
    {
        //Mock request
        $request = Mockery::mock('Request');
        $request->shouldReceive('getHeaders')->andReturn(['Language' => 'th']);

        //register
        $this->di->set('request', $request, true);

        //create class
        $api = new ApiController();
        
        $result = $this->callMethod(
            $api,
            'getLanguageFromHeader',
            []
        );

        //check result
        $this->assertEquals('th', $result);
    }

    //------- end: Test function ---------//
}
