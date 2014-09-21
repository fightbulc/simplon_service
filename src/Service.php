<?php

namespace Simplon\Service;

class Service
{
    /** @var  array */
    protected static $config;

    /**
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     * @throws ErrorException
     */
    public static function start(array $configCommon, array $configEnv = [])
    {
        // set config
        self::setConfig($configCommon, $configEnv);

        // set error handler
        self::setErrorHandler();

        // set exception handler
        self::setExceptionHandler();

        // observe routes
        echo JsonRpcServer::observe(self::getConfigByKeys(['services']));

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
     * @param array $configCommon
     * @param array $configEnv
     *
     * @return bool
     */
    public static function setConfig(array $configCommon, array $configEnv = [])
    {
        self::$config = array_merge($configCommon, $configEnv);

        return true;
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

            $errorResponse = (new ErrorResponse())
                ->setHttpStatusResponseInternalError()
                ->setMessage('Internal error occured')
                ->setData($error);

            echo JsonRpcServer::respond($errorResponse);
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
            // test for json message
            $message = json_decode($e->getMessage(), true);

            // has no json
            if ($message === null)
            {
                $message = $e->getMessage();
            }

            $error = [
                'message' => $message,
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];

            $errorResponse = (new ErrorResponse())
                ->setHttpStatusResponseInternalError()
                ->setMessage('An exception occured')
                ->setData($error);

            echo JsonRpcServer::respond($errorResponse);
        });

        return true;
    }
} 