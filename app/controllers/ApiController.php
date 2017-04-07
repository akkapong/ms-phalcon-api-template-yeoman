<?php
namespace App\Controllers;

use Phalcon\DI;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;

class ApiController extends \Phalcon\Mvc\Micro
{

    public function __construct()
    {

    }

    protected function validateApi($rules, $default = [], $input = [])
    {
        $return   = [];
        $validate = $this->validate($input, $rules);

        if (!empty($validate['error']))
        {
            return [
                'msgError'   => $validate['error']->getMessage(),
                'fieldError' => $validate['error']->getField(),
            ];
        }

        foreach (array_keys($default) as $s_value)
        {

            if (isset($input[$s_value]) && !empty($input[$s_value]))
            {
                $return[$s_value] = $input[$s_value];
            }
            else
            {
                $return[$s_value] = $default[$s_value];
            }

        }

        return array_merge($input, $return);
    }

    protected function validate($input, $rules)
    {
        $validation = new Validation();

        foreach ($rules as $value)
        {

            switch (strtolower($value['type']))
            {

                case 'required':

                    foreach ($value['fields'] as $field)
                    {
                        $validation->add($field, new PresenceOf([
                            'message' => 'The ' . $field . ' is required',
                        ]));
                    }

                    break;

                case 'number':

                    foreach ($value['fields'] as $field)
                    {
                        $validation->add($field, new Numericality([
                            'message' => ucfirst($field) . ' must be numberic',
                        ]));
                    }

                    break;

                default:
                    //default
            }

        }

        $messages = $validation->validate($input);

//Fail
        foreach ($messages as $message)
        {

            return ['error' => $message];
        }

        //Success: no error
        return [];
    }

    protected function validateError($fieldError, $msgError, $statusCode = null)
    {
        $status = $this->status;

        if (!empty($statusCode))
        {
            $code       = 'code' . $statusCode;
            $text       = 'text' . $statusCode;
            $statusCode = $status[$code];
            $statusText = $status[$text];
        }
        else
        {
            $statusCode = $status['code400'];
            $statusText = $status['text400'];
        }

        $output = [
            'status' => [
                'code'    => $statusCode,
                'message' => $statusText,
            ],
            'error'  => [
                'message' => $msgError,
            ],
        ];

        if (isset($fieldError) && !empty($fieldError))
        {
            $output['error']['property'] = $fieldError;
        }

        return $this->responseData($output, $statusCode, $msgError);
    }

    protected function validateBussinessError($field)
    {
        $errorMsg = $this->message;

        return $this->validateError(
            isset($errorMsg[$field]['fieldError']) ? $errorMsg[$field]['fieldError'] : '',
            $errorMsg[$field]['msgError'],
            $errorMsg[$field]['code']
        );
    }

    protected function responseData($data, $status_code = 200, $message = 'Success')
    {
        $this->response->setContentType("application/json", "UTF-8");
        $this->response->setStatusCode($status_code, $message);
        $this->response->setJsonContent($data);

        return $this->response;
    }

    protected function output($output = [], $params = [], $code = 200)
    {
        $status     = $this->status;
        $statusCode = $status['code200'];
        $statusText = $status['text200'];

        $returnOutput = [
            'status' => [
                'code' => $statusCode,
                'text' => $statusText,
            ],
            'data'   => $output,
        ];

        //add limit offset to total 
        if (isset($params['limit'])) {
            $returnOutput['total']['limit']       = (int)$params['limit'];
        }
        if (isset($params['offset'])) {
            $returnOutput['total']['offset']      = (int)$params['offset'];
        }
        if (isset($params['totalRecord'])) {
            $returnOutput['total']['totalRecord'] = (int)$params['totalRecord'];
        }

        return $this->responseData($returnOutput, $code);
    }

    // //Method for get post paramerter
    // protected function getPostInput()
    // {
    //     //get request
    //     $request = DI::getDefault()->get('request');
    //     $inputs  = $request->getPost();

    //     return $inputs;
    // }

    //Method for get post paramerter
    public function getPostInput()
    {
        //get request
        $request = DI::getDefault()->get('request');

        $rawInput = $request->getRawBody();
        //convert to array
        $inputs = json_decode($rawInput, true);

        //convert empty to array
        if (empty($inputs))
        {
            $inputs = [];
        }

        return $inputs;
    }

    //Method for get parameter from url
    protected function getUrlParam($keys)
    {
        //Define output
        $params = [];

        foreach ($keys as $key)
        {
            $params[$key] = $this->request->get($key);
        }

        return $params;
    }

    //Method for get all parameter
    protected function getAllUrlParam()
    {
        $params = $this->request->get();
        //remobe url
        unset($params["_url"]);

        return $params;
    }

    protected function response(array $field)
    {

        foreach ($field as $k => $v)
        {

            if ($v != true)
            {
                $_error[] = $k;
            }

        }

        if (!empty($_error) && is_array($_error))
        {
            return ['error' => $_error];
        }
        else
        {
            return;
        }

    }

    /**
    * Method for get language from header
    */
    protected function getLanguageFromHeader()
    {
        $headers = $this->request->getHeaders();

        if (isset($headers['Language'])) {
            return $headers['Language'];
        }
        return "en";
    }

}
