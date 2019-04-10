<?php

namespace guifcoelho\NeuralNetworks\Libs;

class Utils
{
    /**
     * Tests if two arrays are equal (dimensions and elements)
     */
    static function checkEqualArrays(array $arr1, array $arr2): bool
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
}