<?php

class ClassDesc extends Handled {
    protected $name;

    protected $fieldDescList = array();

    protected $serialVersionUid;

    protected $flags;

    protected $annotations;

    protected $superClassDesc;

    function getAnnotations() {
        return $this->annotations;
    }

    function getSuperClassDesc() {
        return $this->superClassDesc;
    }

    function setAnnotations($annotations) {
        $this->annotations = $annotations;
    }

    function setSuperClassDesc($superClassDesc) {
        $this->superClassDesc = $superClassDesc;
    }


    public function getFlags() {
        return $this->flags;
    }

    public function setFlags($flags) {
        $this->flags = $flags;
        return $this;
    }

    public function getSerialVersionUid() {
        return $this->serialVersionUid;
    }

    public function setSerialVersionUid($serialVersionUid) {
        $this->serialVersionUid = $serialVersionUid;
        return $this;
    }

    public function getFieldDescList() {
        return $this->fieldDescList;
    }

    public function setFieldDescList($fieldDescList) {
        $this->fieldDescList = $fieldDescList;
        return $this;
    }

    /**
     *
     * @param FieldDesc $fieldDesc
     * @return \ClassDesc
     */
    public function addFieldDesc($fieldDesc) {
        $this->fieldDescList[$fieldDesc->getName()] = $fieldDesc;

        return $this;
    }

    public function getAllDeclaredFields() {
        $fields = array();

        if ($this->getSuperClassDesc() != null) {
            $fields = $this->getSuperClassDesc()->getAllDeclaredFields();
        }

        return array_merge($fields, $this->getFieldDescList());
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function toString() {
        return
            ToStringHelper::object($this)
                ->ommitNull()
                ->add("name", $this->getName())
                ->add("fieldDescList", $this->getFieldDescList())
                ->add("serialVersionUid", $this->getSerialVersionUid())
                ->add("flags", $this->getFlags()->toString())
                ->toString();
    }

    public function __toString() {
        return $this->toString();
    }
}