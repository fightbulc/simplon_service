<?php

namespace Simplon\Service;

use Simplon\Error\ErrorHandler;
use Simplon\Error\ErrorResponse;

class Service
{
    /**
     * @var array
     */
    private static $config;

    /**
     * @param array $services
     * @param array $configCommon
     * @param array $configEnv
     * @param null $errorHeaderCallback
     *
     * @return string
     */
    public static function start(array $services, array $configCommon, array $configEnv = [], $errorHeaderCallback = null)
    {
        // handle errors
        self::handleScriptErrors();
        self::handleFatalErrors();
        self::handleExceptions();

        // set config
        self::setConfig($configCommon, $configEnv);

        // observe routes
        return JsonRpcServer::observe($services, $errorHeaderCallback);
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return (array)self::$config;
    }

    /**
     * @param array $keys
     *
     * @return array|bool
     * @throws ErrorException
     */
    public static function getConfigByKeys(array $keys)
    {
        $config = self::getConfig();
        $keysString = join(' => ', $keys);

        while ($key = array_shift($keys))
        {
            if (isset($config[$key]) === false)
            {
                throw new ErrorException('Config entry for [' . $keysString . '] is missing.');
            }

            $config = $config[$key];
        }

        if (!empty($config))
        {
            return $config;
        }

        return false;
    }

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    private static function setConfig(array $configCommon, array $configEnv = [])
    {
        self::$config = array_merge($configCommon, $configEnv);

        return true;
    }

    /**
     * @return void
     */
    private static function handleScriptErrors()
    {
        ErrorHandler::handleScriptErrors(
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleFatalErrors()
    {
        ErrorHandler::handleFatalErrors(
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }

    /**
     * @return void
     */
    private static function handleExceptions()
    {
        ErrorHandler::handleExceptions(
            function (ErrorResponse $errorResponse) { return JsonRpcServer::respond($errorResponse); }
        );
    }
} 