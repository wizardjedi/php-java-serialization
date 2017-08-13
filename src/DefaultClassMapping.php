<?php

class DefaultClassMapping implements Mapping {
    public function java2php($className, $value) {
        switch ($className) {
            case JavaTypes::TYPE_BOOLEAN:

            case JavaTypes::TYPE_BYTE:

            case JavaTypes::TYPE_DOUBLE:
            case JavaTypes::TYPE_FLOAT:

            case JavaTypes::TYPE_SHORT:
            case JavaTypes::TYPE_INTEGER:
            case JavaTypes::TYPE_LONG:

            case JavaTypes::TYPE_STRING:
            default: throw new Exception("Not implemented");
        }
    }

    public function php2java($className, $value) {
        switch ($className) {
            case JavaTypes::TYPE_BOOLEAN:

            case JavaTypes::TYPE_BYTE:

            case JavaTypes::TYPE_DOUBLE:
            case JavaTypes::TYPE_FLOAT:

            case JavaTypes::TYPE_SHORT:
            case JavaTypes::TYPE_INTEGER:
            case JavaTypes::TYPE_LONG:

            case JavaTypes::TYPE_STRING:
            default: throw new Exception("Not implemented");
        }
    }
}
