<?php

namespace libs;

/** Contains all static functions relative to string and array formatting.
 *
 */
class FormatLib {

    /** Enhanced version of implode, including string format.
     * https://www.php.net/manual/en/function.sprintf.php
     * @param array $arr base array to convert to string
     * @param string $format format used for array values
     * @param string $sep array value separator
     * @return string
     */
    static function FormatImplode(array $arr, string $format="%s", string $sep=", "): string
    {
        $result = "";
        for ($i = 0; $i < count($arr); $i++) {
            $result .= sprintf($format, $arr[$i]);
            if ($i != count($arr) - 1) {
                $result .= $sep;
            }
        }
        return $result;
    }

    /** Checks if an element (or an array of element) are of a valid type.
     * @param $tested .element or array of element to check.
     * @param array $valid_types array of accepted types for $tested value(s).
     * @param bool $is_array if true, checks the validity of each and every element inside $tested as array.
     * @return bool
     */
    static function isValidTypeOnly($tested, bool $is_array=false,array $valid_types=["integer", "string", "double"]): bool
    {
        // If $tested not explicitely asserted as array, checks its own type.
        if (!$is_array) {
            return in_array(gettype($tested), $valid_types);
        }
        // Else, checks every value in $tested as array.
        foreach ($tested as $value) {
            if (!in_array(gettype($value), $valid_types)) {
                return false;
            }
        }
        return true;
    }

    /** Enhanced version of implode, including the possibility to have a starting and a finishing character.
     * @param array $arr base array to convert to string
     * @param string $start starting character
     * @param string $end ending character
     * @param string $sep array values separator
     * @return string final string
     */
    static function SurroundImplode(array $arr, string $start="[", string $end="]", string $sep=", "): string
    {
        return "[".implode($sep, $arr)."]";
    }

    /** Converts an array to a valid PDO "insert values" format
     * ie "username = ?, password = ?, email = ?" etc.
     * @param array $arr base array to convert to string
     * @return string final string
     */
    static function ArrayToInsertFormat(array $arr): string
    {
        $result = [];
        foreach ($arr as $key => $value) {
            $result[] = "$key = ?";
        }
        return implode(", ", $result);
    }
}