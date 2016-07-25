<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = 'D:\Leifos\WAMP\htdocs\leifos\svy_results\libs\composer\bin\phantomjs.exe';
    const DIR = 'D:\Leifos\WAMP\htdocs\leifos\svy_results\libs\composer\bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
