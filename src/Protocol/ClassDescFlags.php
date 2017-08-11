<?php

class ClassDescFlags {
    protected $flags;

    function __construct($flags) {
        $this->flags = $flags;
    }

    public function isWriteMethod() {
        return ($this->flags & Constants::SC_WRITE_METHOD) > 0;
    }

    public function isBlockData() {
        return ($this->flags & Constants::SC_BLOCK_DATA) > 0;
    }

    public function isSerializable() {
        return ($this->flags & Constants::SC_SERIALIZABLE) > 0;
    }

    public function isExternalizable() {
        return ($this->flags & Constants::SC_EXTERNALIZABLE) > 0;
    }

    public function isEnum() {
        return ($this->flags & Constants::SC_ENUM) > 0;
    }

    public function getFlags() {
        $result = array();

        if ($this->isWriteMethod()) {
            $result[] = 'SC_WRITE_METHOD';
        }

        if ($this->isBlockData()) {
            $result[] = 'SC_BLOCK_DATA';
        }

        if ($this->isSerializable()) {
            $result[] = 'SC_SERIALIZABLE';
        }

        if ($this->isExternalizable()) {
            $result[] = 'SC_EXTERNALIZABLE';
        }

        if ($this->isEnum()) {
            $result[] = 'SC_ENUM';
        }

        return $result;
    }

    public function __toString() {
        return "ClassDescFlags[".implode(',', $this->getFlags())."]";
    }

}