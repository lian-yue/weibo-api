<?php
namespace LianYue\WeiboApi;


class Response
{

    protected $statusCode = 200;

    protected $headers = [];

    protected $body;

    public function __construct($statusCode, array $headers = array(), $body = null)
    {
        $this->statusCode = (int) $statusCode;
        $this->headers = $headers;
        $this->body = (string) $body;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        return empty($this->headers[$name]) ? [] : $this->headers[$name];
    }

    public function getHeaderLine($name, $default = null)
    {
        return empty($this->headers[$name]) ? $default : implode(',', $this->headers[$name]);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function get()
    {
        return $this->body;
    }

    public function getJson($assoc = false)
    {
        if (!$this->body) {
            return [];
        }
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $json = json_decode(trim($this->body), $assoc, 512, JSON_BIGINT_AS_STRING);
        } else {
            $json = json_decode(trim($this->body), $assoc);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RequestException(function_exists('json_last_error_msg') ? json_last_error_msg() : 'Json decode error', json_last_error());
        }
        return $json;
    }
}
