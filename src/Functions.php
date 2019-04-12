<?php

namespace guifcoelho\NeuralNetworks;

class Functions
{
    /**
     * Returns the value for the sigmoid or its derivative
     * 
     * @var float $value
     * @var bool $derivative
     */
    public static function sigmoid(float $value, bool $derivative = false): float
    {
        $value = 1/(1 + exp(-$value));
        if($derivative){
            return $value*(1-$value);
        }
        return $value;
    }

    /**
     * Calculates the binary cross entropy cost function
     * 
     * @var array $Y
     * @var array $Y_hat
     * @return float
     */
    public static function binary_cross_entropy(array $Y, array $Y_hat): float
    {
        $N = count($Y);
        $sum = 0;
        for($i=0; $i < $N; $i++){
            $sum += $Y[$i] * log($Y_hat[$i]) + (1-$Y[$i]) * log(1 - $Y_hat[$i]);
        }
        return -(1/$N) * $sum;
    }
}