<?php
declare(strict_types=1);

namespace guifcoelho\NeuralNetworks\Libs;

class Helpers
{
    /**
     * Tests if two arrays are equal (dimensions and elements)
     */
    public static function checkEqualArrays(array $arr1, array $arr2): bool
    {
        //Checks dimensions of the arrays
        if(count($arr1) != count($arr2)){
            return false;
        }

        //Iterates over elements
        forEach($arr1 as $key1 => $el1)
        {    
            $el2 = $arr2[$key1];
            //If elements are arrays then recursively tests if array elements are equal
            if(is_array($el1)){
                self::checkEqualArrays($el1, $el2);
            }
            
            //If elements are not arrays then tests element values
            if($el1 != $el2){
                return false;
            }
        }

        //If code is here then arrays are equal
        return true;
    }

    /**
     * Generates an array of random numbers
     * 
     * @param int $min
     * @param int $max
     * @param int $size
     * @return array
     */
    public static function array_of_random_numbers(int $size): array
    {
        $arr = [];
        for($i=0;$i<$size;$i++){
            $x = mt_rand()/mt_getrandmax();
            $y = mt_rand()/mt_getrandmax();
            
            $arr[] = sqrt(-2*log($x))*cos(2*pi()*$y)*1 + 0;
        }
        return $arr;
    }

    /**
     * Searchs an array for a value
     */
    public static function find_in_array(array $arr, $value): bool
    {
        foreach($arr as $el){
            if($el === $value)
                return true;
        }
        return false;
    }

}