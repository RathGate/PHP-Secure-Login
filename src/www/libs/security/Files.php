<?php

namespace libs\security;

/**
 * Class ollowing to easily access files in and out of the server folder with static methods.
 */
class Files
{
    /**
     * @param string $pathFromRoot File path (including extension if it exists) from
     * the server document root.
     * @return string Full path of the file.
     */
    public static function GetPath(string $pathFromRoot=""): string {
        return $_SERVER["DOCUMENT_ROOT"].$pathFromRoot;
    }

    /**
     * @param string $pathFromSecureRoot File path (including extension if it exists) from
     * the folder above the server document root.
     * @return string Full path of the file.
     */
    public static function GetSecurePath(string $pathFromSecureRoot=""): string {
        return $_SERVER["DOCUMENT_ROOT"]."/../".$pathFromSecureRoot;
    }

    /** File path (including extension if it exists) from
     * the server document root.
     * @param string $pathFromRoot
     * @return false|string Content of the file if exists, false otherwise.
     */
    public static function GetFile(string $pathFromRoot="")
    {
        return file_get_contents(self::GetPath($pathFromRoot));
    }

    /**
     * @param string $pathFromSecureRoot File path (including extension if it exists) from
     * the folder above the server document root.
     * @return false|string Content of the file if exists, false otherwise.
     */
    public static function GetSecureFile(string $pathFromSecureRoot="")
    {
        return file_get_contents(self::GetSecurePath($pathFromSecureRoot));
    }
}