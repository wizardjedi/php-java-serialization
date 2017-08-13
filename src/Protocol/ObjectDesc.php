<?php

class ObjectDesc extends Handled {
    protected $classDesc;

    protected $fields;

    /**
     *
     * @return ClassDesc
     */
    function getClassDesc() {
        return $this->classDesc;
    }

    function setClassDesc($classDesc) {
        $this->classDesc = $classDesc;
        return $this;
    }

    function getFields() {
        return $this->fields;
    }

    function setFields($fields) {
        $this->fields = $fields;
        return $this;
    }

    public function addField($name, $value) {
        $this->fields[$name] = $value;

        return $this;
    }

    /**
     *
     * @param ObjectDesc $objectDesc
     * @return mixed
     */
    public function createObject($objectDesc) {
        $name = $this->getClassDesc()->getName();

        $fullName = str_replace(array('.','$'), "\\", $name);

        $parts = explode("\\", $fullName);

        $nameSpace = implode("\\", array_slice($parts, 0, count($parts)-1));

        $clname = array_slice($parts, count($parts)-1)[0];

        if (class_exists($nameSpace.'\\'.$clname)) {
            $code = '$c = new '.$nameSpace.'\\'.$clname.'();';
        } else {
            $fieldsList = array();

            foreach ($this->getFields() as $name=>$value) {
                $fieldsList[] = "public $".$name.";";
            }

            $code = 'namespace '.$nameSpace.';class '.$clname.'{'.implode("\n", $fieldsList).'} $c= new '.$clname.'();';
        }

        eval($code);

        foreach ($this->getFields() as $name=>$value) {
            if ($value instanceof ObjectDesc) {
                $c->$name = $value->createObject();
            } else {
                $c->$name = $value;
            }
        }

        return $c;
    }

    public function __toString() {

    }

}