<?php

class ObjectInputStream {
    protected $buffer;

    protected $length = 0;

    protected $offset = 0;

    protected $cache;

    protected $handle;

    protected $headerChecked = false;

    function __construct($buffer) {
        $this->buffer = $buffer;

        $this->length = strlen($buffer);
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

        $tag = Bits::readByte($this->readData(1));

        if ($tag == Constants::TC_OBJECT) {
            $this->readClassDesc();

            $this->newHandle();

            $this->readClassData();


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
        $this->readNewClassDesc();

        throw new Exception("readClassDesc not implemented");
    }

    /**
     *  newClassDesc:
     *    TC_CLASSDESC className serialVersionUID newHandle classDescInfo
     *    TC_PROXYCLASSDESC newHandle proxyClassDescInfo
     * @throws Exception
     */
    public function readNewClassDesc() {
        $tag = Bits::readByte($this->readData(1));

        if ($tag == Constants::TC_CLASSDESC) {
            $className = $this->readClassName();
            $serialVersionUid = Bits::readLong($this->readData(Constants::LENGTH_LONG));

            $this->newHandle();

            $this->readClassDescInfo();
        } else {
            throw new Exception("Unrecognized tag:".$tag);
        }

        throw new Exception("readNewClassDesc not implemented");
    }

    /**
     *  classDescInfo:
     *    classDescFlags fields classAnnotation superClassDesc
     * @throws Exception
     */
    public function readClassDescInfo() {
        $flags = $this->readClassDescFlags();

        $this->readFields();

        $this->readClassAnnotation();

        $this->readSuperClassDesc();

        throw new Exception("readClassDescInfo not implemented");
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

        throw new Exception("readClassAnnotation not implemented");
    }

    public function readEndBlockData() {
        $tag = Bits::readByte($this->readData(Constants::LENGTH_BYTE));

        if ($tag == Constants::TC_ENDBLOCKDATA) {
            return ;
        } else {
            throw new Exception("Not a end block.");
        }
    }

    /**
     *  fields:
     *    (short)<count>  fieldDesc[count]
     */
    public function readFields() {
        $fieldCount = Bits::readShort($this->readData(Constants::LENGTH_SHORT));

        for ($i=0;$i<$fieldCount;$i++) {
            $this->readFieldDesc();
        }
    }

    /**
     *  fieldDesc:
     *    primitiveDesc
     *    objectDesc
     */
    public function readFieldDesc() {
        try {
            $fieldDesc = $this->readPrimitiveDesc();

            return $fieldDesc;
        } catch (Exception $e) {
            try {
                $fieldDesc = $this->readObjectDesc();

                return $fieldDesc;
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        throw new Exception("readFieldDesc not implemented");
    }

    /**
     * primitiveDesc:
     *  prim_typecode fieldName
     * @throws Exception
     */
    public function readPrimitiveDesc() {
        $typeCode = $this->readData(Constants::LENGTH_BYTE);

        if (in_array($typeCode, Constants::$PRIMITIVE_TYPE_CODES)) {
            $fieldName = $this->readString();

            return FieldDesc::create($fieldName, $typeCode);
        } else {
            $offset = $this->offset;
            throw new Exception("Unrecognized primitive type code:${typeCode} at offset ${offset}");
        }
    }

    public function readObjectDesc() {
        throw new Exception("readObjectDesc not implemented");
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
        return $this->readString();
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

    public function readClassData() {
        throw new Exception("readClassData not implemented");
    }

    public function newHandle() {
        return $this->handle++;
    }


    public function readHeader() {
        $this->readMagic();
        $this->readStreamVersion();

        $this->headerChecked = true;
    }

    public function readMagic() {
        $this->checkLength(Constants::LENGTH_MAGIC);

        $str = $this->readData(Constants::LENGTH_MAGIC);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_MAGIC) {
            throw new Exception("Unrecognized magic: ".bin2hex($str));
        }
    }

    public function readStreamVersion() {
        $this->checkLength(Constants::LENGTH_STREAM_VERSION);

        $str = $this->readData(Constants::LENGTH_STREAM_VERSION);

        $unpackedValue = unpack("n", $str);

        if ($unpackedValue[1] != Constants::STREAM_VERSION) {
            throw new Exception("Unrecognized stream version: ".bin2hex($str));
        }
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

        return $str;
    }

    public function readPrimitiveValueByTypeCode($typeCode) {
        switch ($typeCode) {
            case Constants::PRIM_TYPE_CODE_BYTE: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_CHAR: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_DOUBLE: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_FLOAT: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_INTEGER: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_LONG:
                return Bits::readSignedLong($this->readData(Constants::LENGTH_LONG));
            case Constants::PRIM_TYPE_CODE_SHORT: throw new Exception("Not implemented");
            case Constants::PRIM_TYPE_CODE_BOOLEAN: throw new Exception("Not implemented");
            default :
                throw new Exception("unrecognized typecode=".$typeCode);
        }
    }
}

