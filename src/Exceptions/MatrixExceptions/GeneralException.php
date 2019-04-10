<?php

namespace guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions;

use Exception as CoreException;

/**
 * Class Exception
 */
class GeneralException extends CoreException
{
    protected $message = "There was an error during matrix operations";

}

