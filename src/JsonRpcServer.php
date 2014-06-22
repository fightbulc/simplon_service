<?php

namespace Simplon\Service;

class JsonRpcServer
{
    /** @var  [] */
    protected static $request;

    /** @var  string */
    protected static $id;

    /** @var  string */
    protected static $domainName;

    /** @var  string */
    protected static $serviceName;

    /** @var  string */
    protected static $methodName;

    /** @var  [] */
    protected static $params = [];

    /**
     * @return bool
     * @throws Exception
     */
    protected static function validateAndSetup()
    {
        // make sure specifications are cool
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json')
        {
            $requestJson = file_get_contents('php://input');
            $request = json_decode($requestJson, true);

            // if json wasnt malformed
            if ($request !== null)
            {
                $hasCorrectSpecifications =
                    isset($request['jsonrpc'])
                    && $request['jsonrpc'] === '2.0'
                    && isset($request['id'])
                    && isset($request['method'])
                    && isset($request['params']);

                if ($hasCorrectSpecifications === true)
                {
                    // cache request
                    self::$request = $request;

                    // set id
                    self::$id = $request['id'];

                    // set service parts
                    list(self::$domainName, self::$serviceName, self::$methodName) = explode('.', $request['method']);

                    // set params
                    if (!empty($request['params']))
                    {
                        self::$params = $request['params'];
                    }

                    return true;
                }
            }
        }

        throw new Exception('Malformed request data', 100000);
    }

    /**
     * @param array $response
     * @param string $type
     *
     * @return string
     */
    public static function respond(array $response, $type = 'success')
    {
        $data = [
            'jsonrpc' => '2.0',
            'id'      => self::$id
        ];

        if ($type === 'success')
        {
            $data['result'] = $response;
        }
        else
        {
            $data['error'] = $response;
        }

        return json_encode($data);
    }

    /**
     * @param array $services
     *
     * @return string
     * @throws Exception
     */
    public static function observe(array $services)
    {
        // validate and setup
        self::validateAndSetup();

        // --------------------------------------

        // test if service exists
        if (isset($services[self::$request['method']]))
        {
            // set service
            $service = $services[self::$request['method']];

            // run service callback
            $response = call_user_func_array([(new $service), self::$methodName], self::$params);

            // return response
            return self::respond($response);
        }

        // --------------------------------------

        // failed request
        throw new Exception('Invalid service request', 100001);
    }
} 