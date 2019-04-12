<?php

namespace guifcoelho\NeuralNetworks\Exceptions;

use Exception as CoreException;

/**
 * Class Exception
 */
class DatasetException extends CoreException
{
    protected $message = "Error when loading dataset";
}

