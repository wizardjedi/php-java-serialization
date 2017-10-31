<?php

class ObjectUtil {
    public static function getClassNameInfo($javaClassName) {
        $fullName = str_replace(array('.','$'), "\\", $javaClassName);

        $parts = explode("\\", $fullName);

        if (count($parts) == 1) {
            return $fullName;
        } else {

            $nameSpace = implode("\\", array_slice($parts, 0, count($parts)-1));

            $clname = array_slice($parts, count($parts)-1)[0];

            return
                array(
                    $nameSpace,
                    $clname
                );
        }
    }

    public static function getNamespaceByInfo($info) {
        if (is_array($info)) {
            return $info[0];
        } else {
            return "";
        }
    }

    public static function getClassnameByInfo($info) {
        if (is_array($info)) {
            return $info[1];
        } else {
            return $info;
        }
    }
}