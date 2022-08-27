<?php

namespace App\Util;

use App\Models\ReferralCode;

class Helper
{
    public static function generate_random_str(
        $length, 
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    )
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function generateReferral($name)
    {
        $suffix = self::generate_random_str(3);
        $prefix = substr($name, 0, 3);
        $referralCode = strtoupper($prefix . $suffix);

        $refExist = ReferralCode::where('code', $referralCode)->get();
        if ($refExist->count() > 0) {
            $numExist = $refExist->count() + 1;
            self::generateReferral("{$numExist}{$name}");
        }

        return $referralCode;
    }
}
