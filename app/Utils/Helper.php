<?php

namespace App\Utils;

use Random\RandomException;

class Helper
{

    /**
     * @throws RandomException
     */
    public static function generateSenderAccountNumber(): string
    {
        return (string) random_int(23432434, 54395385743853);
    }

}
