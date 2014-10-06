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

    /** @var  array */
    protected static $params = [];

    /**
     * @return bool|ErrorResponse
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

        // failed request
        return (new ErrorResponse())
            ->setHttpStatusRequestMalformed()
            ->setMessage('Malformed request data')
            ->setData(['requestData' => self::$request]);
    }

    /**
     * @param array|string|ErrorResponse $response
     *
     * @return string
     */
    public static function respond($response)
    {
        if (is_string($response) === true)
        {
            // in case we respond with text (e.g. cached content)
            $jsonResponse = '{"jsonrpc":"2.0", "id":' . self::$id . ', "result":' . $response . '}';
        }
        else
        {
            $data = [
                'jsonrpc' => '2.0',
                'id'      => self::$id
            ];

            if ($response instanceof ErrorResponse)
            {
                // set http status
                http_response_code($response->getHttpStatusCode());

                // set error data
                $data['error'] = $response->getResponse();
            }
            else
            {
                $data['result'] = $response;
            }

            $jsonResponse = json_encode($data);
        }

        return $jsonResponse;
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

        if($response !== true)
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
            return self::respond($response);
        }

        // --------------------------------------

        // failed request
        $errorResponse = (new ErrorResponse())
            ->setHttpStatusRequestNotFound()
            ->setMessage('Method not found')
            ->setData(['requestData' => self::$request]);

        return self::respond($errorResponse);
    }
} 