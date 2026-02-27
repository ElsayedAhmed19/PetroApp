<?php

namespace App\Exceptions;

use Exception;

class DuplicateEventException extends Exception
{
    protected $message = 'This event ID has already been processed.';
}
