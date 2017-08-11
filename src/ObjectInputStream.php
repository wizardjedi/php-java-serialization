<?php

class ObjectInputStream {
    protected $buffer;

    protected $length = 0;

    protected $offset = 0;

    protected $cache;

    protected $handle;

    protected $headerChecked = false;

    protected $logEnabled = false;

    function __construct($buffer) {
        $this->buffer = $buffer;

        $this->length = strlen($buffer);
    }

    public function log($msg, ...$args) {
        if ($this->logEnabled) {
            $str = call_user_func_array("sprintf", array_merge(array($msg), $args));

            echo "offset:".$this->offset." => ".$str."\n";
        }
    }

    /**
     *  object:
     *    newObject
     *    newClass
     *    newArray
     *    newString
     *    newEnum
     *    newClassDesc
     *    prevObject
     *    nullReference
     *    exception
     *    TC_RESET
     *
     *
     * @throws Exception
     */
    public function readObject() {
        if (!$this->headerChecked) {
            $this->readHeader();
        }

        $this->log("Try to read object");

        $tag = Bits::readByte($this->readData(1));

        $this->log("Got tag %s", dechex($tag));

        if ($tag == Constants::TC_OBJECT) {
            $this->log("Try to process TC_OBJECT");

            $classDesc = $this->readClassDesc();

            $this->log("Read class desc:%s", $classDesc->__toString());

            $newObjectDesc = new ObjectDesc();
            $newObjectDesc->setClassDesc($classDesc);

            $this->newHandle($newObjectDesc);

            $objectDesc = $this->readClassData($newObjectDesc);

            $obj = $objectDesc->createObject();

            return $obj;
        } else {
            throw new Exception("Unrecognized tag:".$tag);
        }

        throw new Exception("readObject not implemented");
    }

    /**
     * classDesc:
     *  newClassDesc
     *  nullReference
     *  (ClassDesc)prevObject      // an object required to be of type
     *                             // ClassDesc
     * @throws Exception
     */
    public function readClassDesc() {
        $this->log("Try to read classDesc");

        return $this->readNewClassDesc();
    }

    /**
     *  newClassDesc:
     *    TC_CLASSDESC className serialVersionUID newHandle classDescInfo
     *    TC_PROXYCLASSDESC newHandle proxyClassDescInfo
     * @throws Exception
     */
    public function readNewClassDesc() {
        $this->log("Try to read newClassDesc");

        $tag = Bits::readByte($this->readData(1));

        $this->log("Got tag:%s", dechex($tag));

        if ($tag == Constants::TC_CLASSDESC) {
            $this->log("Process TC_CLASSDESC");

            $className = $this->readClassName();

            $this->log("Try to read serialVersionUid");
            $serialVersionUid = Bits::readLong($this->readData(Constants::LENGTH_LONG));
            $this->log("Got serial version UID:%d", $serialVersionUid);

            $newClassDesc = new ClassDesc();
            $newClassDesc->setName($className);
            $newClassDesc->setSerialVersionUid($serialVersionUid);

            $this->newHandle($newClassDesc);

            $this->readClassDescInfo($newClassDesc);

            return $newClassDesc;
        } else if ($tag == Constants::TC_NULL) {
            return ;
        } else {
            throw new Exception("Unrecognized tag:".dechex($tag));
        }
    }

    /**
     *  classDescInfo:
     *    classDescFlags fields classAnnotation superClassDesc
     * @throws Exception
     */
    public function readClassDescInfo(ClassDesc $classDesc) {
        $this->log("Try to read classDescInfo");

        $flags = $this->readClassDescFlags();

        $this->log("Read flags:%s", $flags->__toString());

        $classDesc->setFlags($flags);

        $this->log("Try to read fields");
        $this->readFields($classDesc);

        $this->readClassAnnotation();

        $this->readSuperClassDesc();
    }

    /**
     * superClassDesc:
     *   classDesc
     */
    public function readSuperClassDesc() {
        $this->readClassDesc();
    }

    /**
     *classAnnotation:
     *  endBlockData
     *  contents endBlockData      // contents written by annotateClass
     */
    public function readClassAnnotation() {
        $this->readEndBlockData();
    }

    public function readEndBlockData() {
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        if ($tag == Constants::TC_ENDBLOCKDATA) {
            return ;
        } else {
            throw new Exception("Not a end block.");
        }
    }

    public function readNull() {
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        if ($tag == Constants::TC_NULL) {
            return ;
        } else {
            throw new Exception("Not a null.");
        }
    }

    /**
     *  fields:
     *    (short)<count>  fieldDesc[count]
     */
    public function readFields(ClassDesc $classDesc) {
        $this->log("Try to read fields");

        $fieldCount = Bits::readShort($this->readData(Constants::LENGTH_SHORT));
        $this->log("Got field count:%d", $fieldCount);

        for ($i=0;$i<$fieldCount;$i++) {
            $fieldDesc = $this->readFieldDesc();

            $classDesc->addFieldDesc($fieldDesc);
        }

        var_dump($classDesc);die;

        return $classDesc;
    }

    /**
     *  fieldDesc:
     *    primitiveDesc
     *    objectDesc
     */
    public function readFieldDesc() {
        $this->log("Try to read field desc");

        $typeCode = $this->readData(Constants::LENGTH_BYTE);
        $this->log("Got type code:%s", $typeCode);

        $isPrimitive = in_array($typeCode, Constants::$PRIMITIVE_TYPE_CODES);
        $isObject = in_array($typeCode, Constants::$OBJECT_TYPE_CODES);

        $this->log("isPrimitive:%s isObject:%s", $isPrimitive, $isObject);

        if ($isPrimitive || $isObject) {
            if ($isPrimitive) {
                return $this->readPrimitiveDesc($typeCode);
            } else {
                return $this->readObjectDesc($typeCode);
            }
        } else {
            $offset = $this->offset;
            throw new Exception("Unrecognized type code:${typeCode} at offset ${offset}");
        }
    }

    /**
     * primitiveDesc:
     *  prim_typecode fieldName
     * @throws Exception
     */
    public function readPrimitiveDesc($typeCode) {
        $fieldName = $this->readString();

        return FieldDesc::create($fieldName, $typeCode);
    }

    public function readObjectDesc($typeCode) {
        $this->log("Try to read objectDesc");

        $this->log("Try to read fieldName");
        $fieldName = $this->readString();
        $this->log("Got field name:%s", $fieldName);

        $this->log("Try to read className");
        $className = $this->readString();
        $this->log("Got className:%s", $className);

        $fieldDesc = FieldDesc::create($fieldName, $typeCode);

        $fieldDesc->setClassName($className);

        return $fieldDesc;
    }

    public function readClassDescFlags() {
        $flags = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        $classDescFlags = new ClassDescFlags($flags);

        return $classDescFlags;
    }

    /**
     *  className:
     *    (utf)
     */
    public function readClassName() {
        $this->log("Try to read className");

        $str = $this->readString();

        $this->log("Read class name:'%s'", $str);

        return $str;
    }

    /**
     * string:
     *   (utf)
     */
    public function readString() {
        $stringLength = Bits::readShort($this->readData(Constants::LENGTH_SHORT));

        $str = $this->readData($stringLength);

        return $str;
    }

    public function readClassData(ObjectDesc $objectDesc) {
        foreach ($objectDesc->getClassDesc()->getFieldDescList() as $name => $fieldDesc) {
            $value = $this->readPrimitiveValueByTypeCode($fieldDesc->getTypeCode());

            $objectDesc->addField($name, $value);
        }

        return $objectDesc;
    }

    public function newHandle(Handled $object) {
        $id = $this->handle++;

        $object->setHandleId($id);

        $this->cache[$id] = $object;

        return $object;
    }

    public function readHeader() {
        $this->readMagic();
        $this->readStreamVersion();

        $this->headerChecked = true;
    }

    public function readMagic() {
        $this->log("Try to read magic");

        $this->checkLength(Constants::LENGTH_MAGIC);

        $str = $this->readData(Constants::LENGTH_MAGIC);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_MAGIC) {
            throw new Exception("Unrecognized magic: ".bin2hex($str));
        }

        $this->log("Magic %s readed", dechex($unpackedValue[1]));
    }

    public function readStreamVersion() {
        $this->log("Try to read stream version");

        $this->checkLength(Constants::LENGTH_STREAM_VERSION);

        $str = $this->readData(Constants::LENGTH_STREAM_VERSION);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_VERSION) {
            throw new Exception("Unrecognized stream version: ".bin2hex($str));
        }

        $this->log("Stream %s readed", dechex($unpackedValue[1]));
    }

    public function checkLength($len) {
        if ($this->offset + $len > $this->length) {
            throw new Exception("Not enougn data in buffer");
        }
    }

    public function readData($len) {
        $this->log("Try to read %d bytes", $len);

        $this->checkLength($len);

        $str = substr($this->buffer, $this->offset, $len);

        $this->offset += $len;

        $this->log("Read '%s'", bin2hex($str));

        return $str;
    }

    public function readPrimitiveValueByTypeCode($typeCode) {
        switch ($typeCode) {
            case Constants::PRIM_TYPE_CODE_BYTE:
                return Bits::readSigned($this->readData(Constants::LENGTH_BYTE));
            case Constants::PRIM_TYPE_CODE_CHAR:
                return Bits::readChar($this->readData(Constants::LENGTH_CHAR));
            case Constants::PRIM_TYPE_CODE_DOUBLE:
                return Bits::readDouble($this->readData(Constants::LENGTH_DOUBLE));
            case Constants::PRIM_TYPE_CODE_FLOAT:
                return Bits::readFloat($this->readData(Constants::LENGTH_FLOAT));
            case Constants::PRIM_TYPE_CODE_INTEGER:
                return Bits::readSigned($this->readData(Constants::LENGTH_INT));
            case Constants::PRIM_TYPE_CODE_LONG:
                return Bits::readLong($this->readData(Constants::LENGTH_LONG));
            case Constants::PRIM_TYPE_CODE_SHORT:
                return Bits::readSigned($this->readData(Constants::LENGTH_SHORT));
            case Constants::PRIM_TYPE_CODE_BOOLEAN:
                return Bits::readBoolean($this->readDate(Constants::LENGTH_BYTE));
            default :
                throw new Exception("unrecognized typecode=".$typeCode);
        }
    }

    function getLogEnabled() {
        return $this->logEnabled;
    }

    function setLogEnabled($logEnabled) {
        $this->logEnabled = $logEnabled;
        return $this;
    }
}

