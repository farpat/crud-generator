<?php

namespace App\Utilities\Crud;


class CrudException extends \Exception
{
    public function __construct (string $resource)
    {
        $message = 'The class ' . $resource . ' doesn\'t exists! Please create this to resolve that.';
        parent::__construct($message, 0, null);
    }
}