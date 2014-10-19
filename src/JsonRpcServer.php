<?php

namespace Simplon\Service;

use Simplon\Error\ErrorResponse;

class JsonRpcServer
{
    /**
     * @var array
     */
    private static $request;

    /**
     * @var string
     */
    private static $jsonRpcVersion = '2.0';

    /**
     * @var string
     */
    private static $id;

    /**
     * @var string
     */
    private static $domainName;

    /**
     * @var string
     */
    private static $serviceName;

    /**
     * @var string
     */
    private static $methodName;

    /**
     * @var array
     */
    private static $params = [];

    /**
     * @param array|string|ErrorResponse $response
     *
     * @return string
     */
    public static function respond($response)
    {
        // in case we respond with text (e.g. cached content)
        if (is_string($response) === true)
        {
            return '{"jsonrpc":"' . self::$jsonRpcVersion . '", "id":"' . self::$id . '", "result":' . $response . '}';
        }

        // --------------------------------------

        $data = [
            'jsonrpc' => '2.0',
            'id'      => self::$id
        ];

        if ($response instanceof ErrorResponse)
        {
            // set http status
            http_response_code($response->getHttpCode());

            // set error data
            $data['error'] = [
                'message' => $response->getMessage(),
            ];

            // set data
            if ($response->hasData() === true)
            {
                $data['error']['data'] = $response->getData();
            }

            // set code
            if ($response->getCode() !== null)
            {
                $data['code'] = $response->getCode();
            }
        }
        else
        {
            $data['result'] = $response;
        }

        return json_encode($data);
    }

    /**
     * @param array $services
     *
     * @return string
     * @throws ErrorException
     */
    public static function observe(array $services)
    {
        // validate and setup
        $response = self::validateAndSetup();

        if ($response !== true)
        {
            return self::respond($response);
        }

        // --------------------------------------

        // test if service exists
        if (isset($services[self::$domainName . '.' . self::$serviceName]))
        {
            // set service
            $service = $services[self::$domainName . '.' . self::$serviceName];

            // run service callback
            $response = call_user_func_array([(new $service), self::$methodName], self::$params);

            // return response
            if ($response !== null)
            {
                return self::respond($response);
            }
        }

        // --------------------------------------

        // failed request
        $errorResponse = (new ErrorResponse())->requestNotFound(
            'Method not found',
            'JSONRPC_E0001',
            ['requestData' => self::$request]
        );

        return self::respond($errorResponse);
    }

    /**
     * @return bool|ErrorResponse
     */
    private static function validateAndSetup()
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
                    self::$id = (string)$request['id'];

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

        // --------------------------------------

        // failed request
        $errorResponse = (new ErrorResponse())->requestNotFound(
            'Malformed request data',
            'JSONRPC_E0002',
            ['requestData' => self::$request]
        );

        return $errorResponse;
    }
}