<?php

namespace guifcoelho\NeuralNetworks\Exceptions\MatrixExceptions;

use Exception as CoreException;

/**
 * Class Exception
 */
class MultiplicationException extends CoreException
{
    protected $message = "Number of columns of the first matrix does not match with the number of rows of the second one.";

}