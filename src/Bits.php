<?php

class Bits {
    public static function readByte($value) {
        return unpack("C", $value)[1];
    }

    public static function readShort($value) {
        return unpack("n", $value)[1];
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
