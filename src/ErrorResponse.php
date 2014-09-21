<?php

namespace Simplon\Service;

/**
 * ErrorResponse
 * @package Simplon\Service
 * @author Tino Ehrich (tino@bigpun.me)
 */
class ErrorResponse
{
    /**
     * @var int
     */
    protected $httpStatusCode = 200;

    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getResponse()
    {
        $data = [
            'message' => $this->getMessage(),
            'data'    => $this->getData(),
        ];

        if ($this->getErrorCode() > 0)
        {
            $data['code'] = $this->getErrorCode();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return (array)$this->data;
    }

    /**
     * @param array $data
     *
     * @return ErrorResponse
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return (int)$this->errorCode;
    }

    /**
     * @param int $errorCode
     *
     * @return ErrorResponse
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return (int)$this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     *
     * @return ErrorResponse
     */
    protected function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return (string)$this->message;
    }

    /**
     * @param string $message
     *
     * @return ErrorResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusRequestMalformed()
    {
        $this->setHttpStatusCode(400);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusRequestUnauthorised()
    {
        $this->setHttpStatusCode(401);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusRequestForbidden()
    {
        $this->setHttpStatusCode(403);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusRequestNotFound()
    {
        $this->setHttpStatusCode(404);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusRequestMethodNotAllowed()
    {
        $this->setHttpStatusCode(405);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusResponseInternalError()
    {
        $this->setHttpStatusCode(500);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusResponseBadGateway()
    {
        $this->setHttpStatusCode(502);

        return $this;
    }

    /**
     * @return ErrorResponse
     */
    public function setHttpStatusResponseUnavailable()
    {
        $this->setHttpStatusCode(503);

        return $this;
    }
}