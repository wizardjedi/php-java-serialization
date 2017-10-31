<?php

class ObjectInputStream {
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $log;

    protected $buffer;

    protected $length = 0;

    protected $offset = 0;

    protected $cache;

    protected $handle = Constants::baseWireHandle;

    protected $headerChecked = false;

    protected $logEnabled = false;

    protected $depth = 0;

    function __construct($buffer, $logger = null) {
        $this->buffer = $buffer;

        $this->length = strlen($buffer);

        if ($logger != null) {
            $this->log = $logger;
        } else {
            $this->log = new Monolog\Logger(get_class($this));
        }
    }

    /**
     * @return ObjectInputStream
     */
    public function log($msg, ...$args) {
        if ($this->logEnabled) {
            $str = call_user_func_array("sprintf", array_merge(array($msg), $args));

            $this->
                log->
                info(
                sprintf("%6d",$this->offset)
                    .": ["
                    .$this->bufferContents()
                    ."] "
                    .str_repeat(" ", 4*$this->depth)
                    .$str
                );
        }

        return $this;
    }

    public function bufferContents() {
        $data = substr($this->buffer, $this->offset, 8);

        $value = unpack("H*", $data);

        $hexStr = $value[1];

        $str = preg_replace('~(..)~', '$1 ', $hexStr);

        $trimmed = trim($str);

        if (empty($trimmed)) {
            $trimmed = "    -=EMPTY BUFFER=-   ";
        }

        return $trimmed;
    }

    public function startGroup() {
        $this->depth++;

        return $this;
    }

    public function endGroup() {
        if ($this->depth >= 1) {
            $this->depth--;
        }

        return $this;
    }

    /**
     *  object => TAG + objectSpec
     *    [TC_OBJECT] newObject
     *    [TC_CLASS] newClass
     *    [TC_ARRAY] newArray
     *    [TC_STRING] newString
     *    [TC_LONG_STRING] newString
     *    [TC_ENUM] newEnum
     *    [TC_CLASSDESC] newClassDesc
     *    [TC_PROXYCLASSDESC] newClassDesc
     *    [TC_REFERENCE] prevObject
     *    [TC_NULL] nullReference
     *    [TC_EXCEPTION] exception
     *    [TC_RESET]
     *
     *
     * @throws Exception
     */
    public function readObject() {
        if (!$this->headerChecked) {
            $this->log("No header processed. Try to read header.");

            $this->readHeader();
        }

        $this->log("Try to read object")->startGroup();

        $tag = Bits::readByte($this->readData(1));

        $this->log("Got tag %s", Constants::tagMnemonic($tag));

        $result = null;

        switch ($tag) {
            case Constants::TC_OBJECT:
                $result = $this->readNewObject();
                break;
            case Constants::TC_CLASS:
            case Constants::TC_ARRAY:
            case Constants::TC_STRING:
                $result = $this->readString();
                break;
            case Constants::TC_LONG_STRING:
                $result = $this->readLongString();
                break;
            case Constants::TC_ENUM:
            case Constants::TC_CLASSDESC:
            case Constants::TC_PROXYCLASSDESC:
            case Constants::TC_REFERENCE:
            case Constants::TC_NULL:
            case Constants::TC_EXCEPTION:
            case Constants::TC_RESET:
                throw new Exception("Unimplemented alternativ:".$tag);
            default:
                throw new Exception("Unrecognized tag:".$tag);
        }

        $this->log("Finish read object")->endGroup();

        return $result;
    }

    /**
     *  newObject:
     *    TC_OBJECT classDesc newHandle classdata[]  // data for each class
     * @return type
     */
    public function readNewObject() {
        $this->log("Try to process TC_OBJECT")->startGroup();

        $classDesc = $this->readClassDesc();

        $this->log("Read class desc");

        if ($this->logEnabled) {
            echo $classDesc->toString()."\n";
        }

        $newObjectDesc = new ObjectDesc();
        $newObjectDesc->setClassDesc($classDesc);

        $this->newHandle($newObjectDesc);

        $objectDesc = $this->readClassData($newObjectDesc);

        $obj = $objectDesc->createObject($objectDesc);

        $this->log("Finished TC_OBJECT")->endGroup();

        return $obj;
    }

    /**
     *  classDesc => TAG + classDesc:
     *    [TC_CLASSDESC] newClassDesc
     *    [TC_PROXYCLASSDESC] newClassDesc
     *    [TC_NULL] nullReference
     *    [TC_REFERENCE] prevObject      // !!!an object required to be of type ClassDesc
     * @throws Exception
     */
    public function readClassDesc() {
        $this->log("Try to read classDesc")->startGroup();

        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        $this->log("Got tag:%s", Constants::tagMnemonic($tag));

        $result = null;

        switch ($tag) {
            case Constants::TC_CLASSDESC:
                $result = $this->readNewClassDesc();
                break;
            case Constants::TC_PROXYCLASSDESC:
                throw new Exception("Unimplemented alternativ:".$tag);
            case Constants::TC_NULL:
                $result = null;
                break;
            case Constants::TC_REFERENCE:
                throw new Exception("Unimplemented alternativ:".$tag);
            default:
                throw new Exception("Unrecognized tag:".$tag);
        }

        $this->log("Finished classDesc")->endGroup();

        return $result;
    }

    /**
     *  newClassDesc:
     *    TC_CLASSDESC className serialVersionUID newHandle classDescInfo
     * @throws Exception
     */
    public function readNewClassDesc() {
        $this->log("Try to read newClassDesc")->startGroup();

        $className = $this->readClassName();

        $this->log("Try to read serialVersionUid")->startGroup();
        $serialVersionUid = Bits::readLong($this->readData(Constants::LENGTH_LONG));
        $this->log("Got serial version UID:%d", $serialVersionUid)->endGroup();

        $newClassDesc = new ClassDesc();
        $newClassDesc->setName($className);
        $newClassDesc->setSerialVersionUid($serialVersionUid);

        $this->newHandle($newClassDesc);

        $this->readClassDescInfo($newClassDesc);

        $result = $newClassDesc;

        $this->log("Finished newClassDesc")->endGroup();

        return $result;
    }

    /**
     *  classDescInfo:
     *    classDescFlags fields classAnnotation superClassDesc
     * @throws Exception
     */
    public function readClassDescInfo(ClassDesc $classDesc) {
        $this->log("Try to read classDescInfo")->startGroup();

        $this->log("Read classDescFlags")->startGroup();
        $flags = $this->readClassDescFlags();
        $this->log("Read flags:%s", $flags->__toString())->endGroup();

        $classDesc->setFlags($flags);

        $this->readFields($classDesc);

        $this->readClassAnnotation();

        $superClassDesc = $this->readSuperClassDesc();

        $classDesc->setSuperClassDesc($superClassDesc);

        $this->log("Finished classDescInfo")->endGroup();
    }

    /**
     * superClassDesc:
     *   classDesc
     */
    public function readSuperClassDesc() {
        $this->log("Try to read superClassDesc")->startGroup();

        $result = $this->readClassDesc();

        $this->log("Finished superClassDesc")->endGroup();

        return $result;
    }

    /**
     *classAnnotation:
     *  endBlockData
     *  contents endBlockData      // contents written by annotateClass
     */
    public function readClassAnnotation() {
        $this->log("Try to read classAnnotation")->startGroup();

        $result = $this->readEndBlockData();

        $this->log("Finished classAnnotation")->endGroup();

        return $result;
    }

    public function readEndBlockData() {
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        $this->log("Got tag %s", Constants::tagMnemonic($tag));

        if ($tag == Constants::TC_ENDBLOCKDATA) {
            return ;
        } else {
            throw new Exception("Not a end block.");
        }
    }

    public function readNull() {
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        $this->log("Got tag %s", Constants::tagMnemonic($tag));

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
        $this->log("Try to read fields")->startGroup();

        $fieldCount = Bits::readShort($this->readData(Constants::LENGTH_SHORT));
        $this->log("Got field count:%d", $fieldCount);

        for ($i=0;$i<$fieldCount;$i++) {
            $fieldDesc = $this->readFieldDesc();

            $classDesc->addFieldDesc($fieldDesc);
        }

        $this->log("Finished fields")->endGroup();

        return $classDesc;
    }

    /**
     *  fieldDesc:
     *    [typeCode] primitiveDesc
     *    [typeCode] objectDesc
     */
    public function readFieldDesc() {
        $this->log("Try to read field desc")->startGroup();

        $this->log("Read type code")->startGroup();
        $typeCode = $this->readData(Constants::LENGTH_BYTE);
        $this->log("Got type code:%s", $typeCode);

        $isPrimitive = in_array($typeCode, Constants::$PRIMITIVE_TYPE_CODES);
        $isObject = in_array($typeCode, Constants::$OBJECT_TYPE_CODES);

        $this->log("isPrimitive:%s isObject:%s", var_export($isPrimitive, true), var_export($isObject, true));

        $this->endGroup();

        $result = null;

        if ($isPrimitive || $isObject) {
            if ($isPrimitive) {
                $result = $this->readPrimitiveDesc($typeCode);
            } else {
                $result = $this->readObjectDesc($typeCode);
            }
        } else {
            $offset = $this->offset;
            throw new Exception("Unrecognized type code:${typeCode} at offset ${offset}");
        }

        $this->log("Finished fieldDesc")->endGroup();

        return $result;
    }

    /**
     * primitiveDesc:
     *  prim_typecode fieldName
     * @throws Exception
     */
    public function readPrimitiveDesc($typeCode) {
        $fieldName = $this->readUtf();

        return FieldDesc::create($fieldName, $typeCode);
    }

    /**
     *  objectDesc:
     *    obj_typecode fieldName className1
     * @param type $typeCode
     * @return type
     * @throws Exception
     */
    public function readObjectDesc($typeCode) {
        $this->log("Try to read objectDesc")->startGroup();

        $this->log("Try to read fieldName")->startGroup();
        $fieldName = $this->readUtf();
        $this->log("Got field name:%s", $fieldName)->endGroup();

        $this->log("Try to read className")->startGroup();
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        $this->log("Got tag %s", Constants::tagMnemonic($tag));

        switch ($tag) {
            case Constants::TC_STRING:
                $className = $this->readString();
                break;
            case Constants::TC_LONGSTRING:
                $className = $this->readLongString();
                break;
            default:
                throw new Exception("Unrecognized tag:".$tag);
        }

        $this->log("Got className:%s", $className)->endGroup();

        $fieldDesc = FieldDesc::create($fieldName, $typeCode);

        $fieldDesc->setClassName($className);

        $this->log("Finished objectDesc")->endGroup();

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
        $this->log("Try to read className")->startGroup();

        $str = $this->readUtf();

        $this->log("Read class name:'%s'", $str)->endGroup();

        return $str;
    }

    /**
     * string:
     *   (utf)
     */
    public function readUtf() {
        $stringLength = Bits::readShort($this->readData(Constants::LENGTH_SHORT));

        $str = $this->readData($stringLength);

        return $str;
    }

    public function readString() {
        $stringLength = Bits::readShort($this->readData(Constants::LENGTH_SHORT));

        $str = $this->readData($stringLength);

        return $str;
    }

    public function readLongString() {
        $stringLength = Bits::readLong($this->readData(Constants::LENGTH_LONG));

        $str = $this->readData($stringLength);

        return $str;
    }

    public function readClassData(ObjectDesc $objectDesc) {
        $classDesc = $objectDesc->getClassDesc();

        $fields = $classDesc->getAllDeclaredFields();

        foreach ($fields as $name => $fieldDesc) {
            $value = $this->readPrimitiveValueByTypeCode($fieldDesc->getTypeCode());

            $objectDesc->addField($name, $value);
        }

        return $objectDesc;
    }

    public function newHandle(Handled $object) {
        $id = $this->handle++;

        $this->log("Object saved with id:%d", $id);

        $object->setHandleId($id);

        $this->cache[$id] = $object;

        return $object;
    }

    public function readHeader() {
        $this->log("Try to read header")->startGroup();

        $this->readMagic();
        $this->readStreamVersion();

        $this->headerChecked = true;

        $this->log("Header ok")->endGroup();
    }

    public function readMagic() {
        $this->log("Try to read magic")->startGroup();

        $this->checkLength(Constants::LENGTH_MAGIC);

        $str = $this->readData(Constants::LENGTH_MAGIC);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_MAGIC) {
            throw new Exception("Unrecognized magic: ".bin2hex($str));
        }

        $this->log("Magic %s readed", dechex($unpackedValue[1]))->endGroup();
    }

    public function readStreamVersion() {
        $this->log("Try to read stream version")->startGroup();

        $this->checkLength(Constants::LENGTH_STREAM_VERSION);

        $str = $this->readData(Constants::LENGTH_STREAM_VERSION);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_VERSION) {
            throw new Exception("Unrecognized stream version: ".bin2hex($str));
        }

        $this->log("Stream version '%s' readed", dechex($unpackedValue[1]))->endGroup();
    }

    public function checkLength($len) {
        if ($this->offset + $len > $this->length) {
            throw new Exception("Not enougn data in buffer");
        }
    }

    public function readData($len) {
        $this->checkLength($len);

        $str = substr($this->buffer, $this->offset, $len);

        $this->offset += $len;

        $this->log("Read %d bytes '%s'", $len, bin2hex($str));

        return $str;
    }

    public function readPrimitiveValueByTypeCode($typeCode) {
        switch ($typeCode) {
            // Primitive types
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
            // Object types
            case Constants::OBJECT_TYPE_CODE_OBJECT:
                $result = $this->readObject();

                return $result;
            case Constants::OBJECT_TYPE_CODE_ARRAY:
            default :
                throw new Exception("unrecognized typecode=".$typeCode);
        }
    }

    public function getLogEnabled() {
        return $this->logEnabled;
    }

    public function setLogEnabled($logEnabled) {
        $this->logEnabled = $logEnabled;
        return $this;
    }

    public function throwException($exception) {
        if ($exception instanceof ObjectStreamException) {
            $exception->setOffset($this->offset);
        }

        throw $exception;
    }
}

