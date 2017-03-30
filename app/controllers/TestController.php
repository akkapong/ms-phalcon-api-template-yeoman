<?php
namespace App\Controllers;

class TestController extends \Phalcon\Mvc\Micro
{
    public function getTestAction()
    {
        //Mock data response
        $datas = [
            'status' => [
                'code' => 200,
                'text' => 'success',
             ],
             'data'   => 'TEST'
        ];
        
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setStatusCode(200, 'success');
        $this->response->setJsonContent($datas);

        return $this->response;
    }
}
        
    