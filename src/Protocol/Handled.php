<?php

class Handled {
    protected $handleId;

    function getHandleId() {
        return $this->handleId;
    }

    function setHandleId($handleId) {
        $this->handleId = $handleId;
        return $this;
    }
}
