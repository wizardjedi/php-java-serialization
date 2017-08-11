<?php

class ToStringHelper {
    protected $clazz;

    protected $ommitNull = false;

    protected $values = array();

    /**
     *
     * @param type $str
     * @return \ToStringHelper
     */
    public static function clazz($str) {
        $helper = new ToStringHelper();
        $helper->setClazz($str);

        return $helper;
    }

    /**
     *
     * @param type $object
     * @return \ToStringHelper
     */
    public static function object($object) {
        $helper = new ToStringHelper();
        $helper->setClazz(get_class($object));

        return $helper;
    }

    /**
     *
     * @param type $name
     * @param type $value
     * @return \ToStringHelper
     */
    public function add($name, $value) {
        $this->values[$name] = $value;

        return $this;
    }

    public function __toString() {
        $this->toString();
    }

    /**
     *
     * @return string
     */
    public function toString() {
        if ($this->ommitNull) {
            $vals =
                array_filter(
                    $this->values,
                    function($el) {
                        return $el != null;
                    }
                );
        } else {
            $vals = $this->values;
        }

        $newVals =
            array_map(
                function($el) {
                    return var_export($el, true);
                },
                $vals
            );

        return
            $this->clazz."[".implode(",", $newVals)."]";
    }

    function getClazz() {
        return $this->clazz;
    }

    function setClazz($clazz) {
        $this->clazz = $clazz;
        return $this;
    }

    /**
     *
     * @return \ToStringHelper
     */
    function ommitNull() {
        $this->ommitNull = true;

        return $this;
    }


}
