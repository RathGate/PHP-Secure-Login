<?php

namespace libs;

/**
 * Contains static sorting methods.
 */
class SortLib {
    // Sorts and returns an array with insertion sorting algorithm.
    static function InsertionSort($arr): false|array
    {
        // Type error check
        if (!is_array($arr)) {
            return false;
        }

        // Sort code
        for ($i = 0; $i < count($arr); $i++) {
            $currentValue = $arr[$i];
            $prevIndex = $i - 1;
            while ($prevIndex >= 0 && $currentValue < $arr[$prevIndex]) {
                $arr[$prevIndex + 1] = $arr[$prevIndex];
                $prevIndex = $prevIndex - 1;
            }
            $arr[$prevIndex+1] = $currentValue;
        }
        return $arr;
    }

    // Sorts and returns an array with bubble sort sorting algorithm.
    static function BubbleSort($arr): false|array
    {
        if (!is_array($arr)) {
            return false;
        }

        for ($i = 0; $i < count($arr); $i++) {
            $hasSwapped = false;
            for ($j = 0; $j < count($arr) - $i - 1; $j++) {
                if ($arr[$j] > $arr[$j+1]) {
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j+1];
                    $arr[$j+1] = $temp;
                    $hasSwapped = true;
                }
            }
            if (!$hasSwapped) {
                break;
            }
        }
        return $arr;
    }

    // Sorts and returns an array with quicksort sorting algorithm.
    static function QuickSort($arr) : false|array
    {
        if (!is_array($arr)) {
            return false;
        }

        if(count($arr) <= 1){
            return $arr;
        }
        else{
            $pivot = $arr[0];
            $left = array();
            $right = array();
            for($i = 1; $i < count($arr); $i++)
            {
                if($arr[$i] < $pivot){
                    $left[] = $arr[$i];
                }
                else{
                    $right[] = $arr[$i];
                }
            }
            return array_merge(self::QuickSort($left), array($pivot), self::QuickSort($right));
        }
    }
}