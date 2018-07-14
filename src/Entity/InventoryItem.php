<?php

namespace App\Entity;

class InventoryItem implements MongoDB\BSON\Persistable
{
    /** @var MongoDB\BSON\ObjectId */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $notes;

    /** @var string[] */
    protected $locations = [];

    /** @var string[] */
    protected $types = [];

    /** @var string */
    protected $purchasePrice;

    /** @var string Individual item value */
    protected $value;

    /** @var int */
    protected $quantity;

    /** @var bool Soft delete */
    protected $deleted = false;

    public function __construct()
    {
        $this->id = new MongoDB\BSON\ObjectId();
    }

    /**
     * Implementation of MongoDB\BSON\Persistable::bosonSerialize
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
    }

    /**
     * Implementation of MongoDB\BSON\Persistable::bosonUnserialize
     */
    public function bsonUnserialize(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key === '_id') {
                $this->id = new MongoDB\BSON\ObjectId($value);
            } else {
                $this->$key = $value;
            }
        }
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

    public function setName(string $name) 
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setNotes(string $notes)
    {
        $this->notes = $notes;
    }

    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * Add one location to the set of locations
     * 
     * @param string $location
     */
    public function addLocation(string $location) 
    {
        $this->locations[] = $location;
    }

    /**
     * Set all locations
     * 
     * @param string[] $locations
     * @throws \RuntimeException
     */
    public function setLocations(array $locations)
    {
        foreach ($locations as $location) {
            if (!is_string($location)) {
                throw new \RuntimeException('All item locations must be strings');
            }
        }
        $this->locations = $locations;
    }

    /**
     * Get all locations associated with this item
     * 
     * @return string[]
     */
    public function getLocations() : array
    {
        return $this->locations;
    }

    /**
     * Add one type to the set of types
     * 
     * @param string $type
     */
    public function addType(string $type) 
    {
        $this->types[] = $type;
    }

    /**
     * Set all types for this item
     * 
     * @param string[] $types
     * @throws \RuntimeException
     */
    public function setTypes(array $types) 
    {
        foreach ($types as $type) {
            if (!is_string($type)) {
                throw new \RuntimeException('All item types must be strings');
            }
        }
        $this->types = $types;
    }

    /**
     * Get all types associated with this item
     * 
     * @return string[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    /**
     * @param string $price
     * @throws \RuntimeException
     */
    public function setPurchasePrice(string $price)
    {
        if (!is_numeric($price)) {
            throw new \RuntimeException('Item price must be numeric');
        }
        $this->purchasePrice = $price;
    }

    public function getPurchasePrice() : string
    {
        return $this->purchasePrice;
    }

    /**
     * Set the individual value of an item
     * 
     * @param string $value
     * @throws \RuntimeException
     */
    public function setValue(string $value)
    {
        if (!is_numeric($value)) {
            throw new \RuntimeException('Item value must be numeric');
        }
        $this->value = $value;
    }

    public function getValue() : string
    {
        return $this->value;
    }

    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function getQuantity() : int
    {
        return $this->quantity;
    }

    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;
    }

    public function isDeleted() : boolean
    {
        return $this->deleted;
    }
}
