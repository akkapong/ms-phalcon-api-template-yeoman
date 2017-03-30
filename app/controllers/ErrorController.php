<?php
namespace App\Controllers;

class ErrorController extends \Phalcon\Mvc\Micro
{

    /**
     * Error 404 (page not found)
     * @return html page error
     */
    public function page404Action()
    {
        $this->response->setContentType("application/json", "UTF-8");

        $this->response->setRawHeader("HTTP/1.1 404 Not Found");
        $this->response->setStatusCode(404, "Not Found");

        $this->response->setJsonContent(array(
            'status'  => '404',
            'message' => 'service not found or unavailable',
            'data'    => '',
        ));

        return $this->response;
    }

}
