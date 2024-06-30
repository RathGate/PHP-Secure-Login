<?php

namespace libs\security;

// Class ollowing to easily access files in and out of the server folder with static methods.
class Files
{
    // File path (including extension if it exists) from
    // the server document root.
    public static function GetPath(string $pathFromRoot=""): string {
        return $_SERVER["DOCUMENT_ROOT"].$pathFromRoot;
    }

    // File path (including extension if it exists) from
    // the folder above the server document root.
    public static function GetSecurePath(string $pathFromSecureRoot=""): string {
        return $_SERVER["DOCUMENT_ROOT"]."/../".$pathFromSecureRoot;
    }

     // File path (including extension if it exists) from
     //  the server document root.
    public static function GetFile(string $pathFromRoot=""): bool|string
    {
        return file_get_contents(self::GetPath($pathFromRoot));
    }

     // File path (including extension if it exists) from
     // the folder above the server document root.
    public static function GetSecureFile(string $pathFromSecureRoot=""): bool|string
    {
        return file_get_contents(self::GetSecurePath($pathFromSecureRoot));
    }
}