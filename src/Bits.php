<?php

class Bits {
    public static function readByte($value) {
        return unpack("C", $value)[1];
    }

    public static function readShort($value) {
        return unpack("n", $value)[1];
    }

    public static function readBoolean($value) {
        return $value != 0;
    }

    public static function readSigned($value) {
        $parts = unpack("C*", $value);

        if ($parts[1] >= 128) {
            $value = -1 << 8;
        } else {
            $value = 0;
        }

        $value = $value | $parts[1];

        if (count($parts) > 1) {
            for($i=2;$i<=count($parts);$i++) {
                $value = $value << 8;
                $value |= $parts[$i];
            }
        }

        return $value;
    }

    public static function readChar($value) {
        return mb_convert_encoding($value, "UTF-8", "UCS-2BE");
    }

    public static function readFloat($value) {
        $parts = unpack("N", $value);

        $bits = $parts[1];

        $s = (($bits >> 31) == 0) ? 1 : -1;

        $e = (($bits >> 23) & 0xff);

        $m = ($e == 0) ?
                      ($bits & 0x7fffff) << 1 :
                      ($bits & 0x7fffff) | 0x800000;

        $floatValue = $s * $m * pow(2, $e - 150);

        return $floatValue;
    }

    public static function readDouble($value) {
        $parts = unpack("J", $value);

        $bits = $parts[1];

        $s = (($bits >> 63) == 0) ? 1 : -1;

        $e = (($bits >> 52) & 0x07ff);

        $m = ($e == 0) ?
                      ($bits & 0xfffffffffffff) << 1 :
                      ($bits & 0xfffffffffffff) | 0x0010000000000000;

        $doubleValue = $s * $m * pow(2, $e - 1075);

        return $doubleValue;
    }

    public static function readLong($value) {
        return unpack("J", $value)[1];
    }

    public static function readSignedLong($value) {
        //$value = strrev($value);

        var_dump($value);die;

        return unpack("q", $value)[1];
    }
}
