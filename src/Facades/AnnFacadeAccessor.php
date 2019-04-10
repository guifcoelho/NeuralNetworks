<?php

namespace guifcoelho\NeuralNetworks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AnnFacadeAccessor
 */
class AnnFacadeAccessor extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'neuralnetworks.ann';
    }
}
