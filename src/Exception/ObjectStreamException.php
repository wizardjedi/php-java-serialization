<?php

class ObjectStreamException extends Exception {
    protected $offset;

    function getOffset() {
        return $this->offset;
    }

    function setOffset($offset) {
        $this->offset = $offset;
    }

    
}
