<?php

namespace App\Utilities\Crud;

use Throwable;

class CrudException extends \Exception
{
    public function __construct (string $resource, $message = "", $code = 0, Throwable $previous = null)
    {
        $message = $resource . ' doesn\'t exists! Please create this to resolve that.';
        parent::__construct($message, $code, $previous);
    }
}