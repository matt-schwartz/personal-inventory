<?php

namespace App\Entity;

class InventoryItem extends Persistable
{
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
    protected $quantity = 1;

    /** @var bool Soft delete */
    protected $deleted = false;

    public function setName(string $name) 
    {
        $this->name = $name;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setNotes(string $notes)
    {
        $this->notes = $notes;
    }

    public function getNotes() : ?string
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

    public function getPurchasePrice() : ?string
    {
        return $this->purchasePrice;
    }

    /**
     * Get total purchase price (individual price * quantity)
     * 
     * @return string|null
     */
    public function getTotalPurchasePrice() : ?string
    {
        $price = null;
        if ($this->purchasePrice && $this->quantity) {
            $price = \bcmul($this->purchasePrice, $this->quantity);
        }

        return $price;
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

    public function getValue() : ?string
    {
        return $this->value;
    }

    /**
     * Get total value (individual value * quantity)
     * 
     * @return string|null
     */
    public function getTotalValue() : ?string
    {
        $value = null;
        if ($this->value && $this->quantity) {
            $value = \bcmul($this->value, $this->quantity);
        }

        return $value;
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
