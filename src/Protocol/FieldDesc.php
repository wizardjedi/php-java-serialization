<?php

class FieldDesc {
    protected $name;
    protected $value;
    protected $typeCode;
    protected $className;

    public static function create($name, $typeCode, $value = null) {
        $a = new FieldDesc();

        $a->setName($name);
        $a->setTypeCode($typeCode);

        if ($value != null) {
            $a->setValue($value);
        }

        return $a;
    }

    public function getClassName() {
        return $this->className;
    }

    public function setClassName($className) {
        $this->className = $className;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    function getTypeCode() {
        return $this->typeCode;
    }

    function setTypeCode($typeCode) {
        $this->typeCode = $typeCode;
        return $this;
    }

    public function __toString() {
        return
            ToStringHelper::object($this)
                ->ommitNull()
                ->add("name", $this->getName())
                ->add("typeCode", $this->getTypeCode())
                ->add("className", $this->getClassName())
                ->add("value", $this->getValue())
                ->toString();
    }

}