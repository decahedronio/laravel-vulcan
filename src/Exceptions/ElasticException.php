<?php namespace Decahedron\Vulcan\Exceptions;

class ElasticException extends \RuntimeException
{
    public $response;

    public function __construct($message = '', $elasticResponse = [], $code = 0, \Throwable $previous = null)
    {
        $this->response = $elasticResponse;
        parent::__construct($message . ' Response: ' . $elasticResponse, $code, $previous);
    }
}