<?php

namespace guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions;

use Exception as CoreException;

/**
 * Class Exception
 */
class DimensionsException extends CoreException
{
    protected $message = "Invalid dimensions";

}