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
        if($derivative){
            return $value*(1-$value);
        }
        return 1/(1 + exp(-$value));
    }

    /**
     * Returns the value for the softmax function
     * 
     * @var float $value
     * @var bool $derivative
     */
    public static function softmax(array $row)
    {
        $expEl = array_map(function($value){
            return exp($value);
        }, $row);
        $new_row = [];
        foreach($expEl as $el){
            $new_row[] = $el/array_sum($expEl);
        }
        return $new_row;
    }

    /**
     * Returns the value for the tanh or its derivative
     * 
     * @var float $value
     * @var bool $derivative
     */
    public static function tanh(float $value, bool $derivative = false): float
    {
        if($derivative){
            return 1-$value*$value;
        }
        return tanh($value);
    }

    /**
     * Calculates the cross entropy cost function for multiple labels
     * 
     * @var array $Y
     * @var array $Y_hat
     * @var int $labels
     * @return float
     */
    public static function cross_entropy(array $Y, array $Y_hat, int $labels): float
    {
        $N = count($Y);
        $sum = 0;
        for($i=0; $i < $N; $i++){
            for($k = 0; $k < $labels; $k++){
                $sum += $Y[$i][$k] * log($Y_hat[$i][$k]);
            } 
        }
        return -(1/$N) * $sum;
    }
}