<?php

class ObjectUtilTest extends PHPUnit_Framework_TestCase
{
    public function testGetClassName() {
        $this->assertEquals("class1", ObjectUtil::getClassNameInfo("class1"));

        $this->
            assertEquals(
                array("java\\lang","Long"),
                ObjectUtil::getClassNameInfo("java.lang.Long")
        );

        $this->
            assertEquals(
                array("java\\util\\Map", "Entry"),
                ObjectUtil::getClassNameInfo("java.util.Map\$Entry")
        );
    }
}
