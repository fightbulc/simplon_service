<?php

namespace Simplon\Service;

use Simplon\Error\ErrorHandler;
use Simplon\Error\ErrorResponse;
use Simplon\Helper\Config;

class Service
{
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
        return Config::getConfig();
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public static function hasConfigKeys(array $keys)
    {
        return Config::hasConfigKeys($keys);
    }

    /**
     * @param array $keys
     *
     * @return array|null
     * @throws \Simplon\Helper\HelperException
     */
    public static function getConfigByKeys(array $keys)
    {
        return Config::getConfigByKeys($keys);
    }

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    private static function setConfig(array $configCommon, array $configEnv = [])
    {
        return Config::setConfig($configCommon, $configEnv);
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