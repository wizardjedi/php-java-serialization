<?php

use Monolog\Logger;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpProperty;
use gossi\codegen\generator\CodeGenerator;

class ObjectDesc extends Handled {
    private $log;

    protected $classDesc;

    protected $fields;

    public function __construct()
    {
        $this->log = new Logger(get_class($this));
    }

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

        $info = ObjectUtil::getClassNameInfo($name);

        $nameSpace = ObjectUtil::getNamespaceByInfo($info);

        $clname = ObjectUtil::getClassnameByInfo($info);

        if (class_exists($nameSpace.'\\'.$clname)) {
            $code = '$c = new '.$nameSpace.'\\'.$clname.'();';
        } else {
            $fieldsList = array();

            foreach ($this->getFields() as $name=>$value) {
                $fieldsList[] = "public $".$name.";";
            }

            $this->log->debug($this->getClassDesc());

            $code = 'namespace '.$nameSpace.';class '.$clname.'{'.implode("\n", $fieldsList).'} $c= new '.$clname.'();';

            $generatingClass = new PhpClass();
            $generatingClass
                ->setName($clname)
                ->setNamespace($nameSpace);

            $generatingClass->setParentClassName($this->getClassDesc()->getSuperClassDesc()->getName());

            foreach ($fieldsList as $field) {
                $generatingClass->setProperty(PhpProperty::create($field));
            }

            $generator = new CodeGenerator(array());
            echo '+++'.$generator->generate($generatingClass).'+++';
        }

        //echo $code."\n";

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
        return "";
    }

}