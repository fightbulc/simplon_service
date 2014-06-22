<?php

namespace Simplon\Service;

class Service
{
    /** @var  array */
    protected static $config;

    /** @var  string */
    protected static $rootPath;

    /**
     * @param array $config
     *
     * @return bool
     * @throws Exception
     */
    public static function start(array $config)
    {
        // set error handler
        self::setErrorHandler();

        // set exception handler
        self::setExceptionHandler();

        // --------------------------------------

        if (isset($config['services']) === false)
        {
            throw new Exception('Config misses: "services" => []');
        }

        // --------------------------------------

        // set root path
        self::$rootPath = rtrim($config['rootPath'], '/') . '/../src/App';

        // set config
        self::setConfig($config);

        // observe routes
        echo JsonRpcServer::observe($config['services']);

        return true;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return (array)self::$config;
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;

        return true;
    }

    /**
     * @param array $keys
     *
     * @return array|bool
     * @throws Exception
     */
    public static function getConfigByKeys(array $keys)
    {
        $config = self::getConfig();
        $keysString = join('-->', $keys);

        while ($key = array_shift($keys))
        {
            if (isset($config[$key]) === false)
            {
                throw new Exception('Config entry for "' . $keysString . '" is missing.');
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
     * @return bool
     */
    public static function setErrorHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline)
        {
            switch ($errno)
            {
                case E_USER_ERROR:
                    $error = [
                        'message' => $errstr,
                        'code'    => null,
                        'data'    => [
                            'type' => 'ERROR',
                            'file' => $errfile,
                            'line' => $errline,
                        ],
                    ];
                    break;

                case E_USER_WARNING:
                    $error = [
                        'message' => "WARNING: $errstr",
                        'code'    => $errno,
                        'data'    => [
                            'type' => 'WARNING'
                        ],
                    ];
                    break;

                case E_USER_NOTICE:
                    $error = [
                        'message' => $errstr,
                        'code'    => $errno,
                        'data'    => [
                            'type' => 'NOTICE',
                        ],
                    ];
                    break;

                default:
                    $error = [
                        'message' => $errstr,
                        'code'    => null,
                        'data'    => [
                            'type' => 'UNKNOWN',
                            'file' => $errfile,
                            'line' => $errline,
                        ],
                    ];
                    break;
            }

            die(JsonRpcServer::respond($error, 'error'));
        });

        return true;
    }

    /**
     * @return bool
     */
    public static function setExceptionHandler()
    {
        set_exception_handler(function (\Exception $e)
        {
            $error = [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'data'    => [
                    'type' => 'EXCEPTION',
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ];

            die(JsonRpcServer::respond($error, 'error'));
        });

        return true;
    }
} 