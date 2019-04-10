<?php

namespace guifcoelho\NeuralNetworks;

/**
 * Class Ann
 */
class Ann
{

    /**
     * @var  \guifcoelho\NeuralNetworks\Config
     */
    private $config;

    /**
     * Sample constructor.
     *
     * @param \guifcoelho\NeuralNetworks\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param $name
     *
     * @return  string
     */
    public function sayHello($name)
    {
        $greeting = $this->config->get('greeting');

        return $greeting . ' ' . $name;
    }

}
