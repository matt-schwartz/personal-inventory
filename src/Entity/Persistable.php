<?php

namespace App\Entity;

use MongoDB\BSON\ObjectId;

abstract class Persistable implements \MongoDB\BSON\Persistable
{
    /** @var \MongoDB\BSON\ObjectId */
    protected $id;

    public function __construct()
    {
        $this->id = new ObjectId();
    }

    /**
     * Implementation of \MongoDB\BSON\Persistable::bsonSerialize
     */
    public function bsonSerialize()
    {
        $data = ['_id' => $this->id];

        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            if ($name !== 'id') {
                $data[$name] = $this->$name;
            }
        }

        return $data;
    }

    /**
     * Implementation of MongoDB\BSON\Persistable::bsonUnserialize
     */
    public function bsonUnserialize(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key === '_id') {
                $this->id = new ObjectId($value);
            } elseif (is_object($value) && is_a($value, 'ArrayObject')) {
                $this->$key = $value->getArrayCopy();
            } else {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get item's Mongo Object ID
     * 
     * @return ObjectId
     */
    public function getObjectId() : ObjectId
    {
        return $this->id;
    }

    /**
     * Get item ID
     * 
     * @return string
     */
    public function getId() : string
    {
        return (string) $this->id;
    }
}
