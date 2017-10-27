<?php

interface Mapping {
    public function java2php($className, $value);
    public function php2java($className, $value);
}