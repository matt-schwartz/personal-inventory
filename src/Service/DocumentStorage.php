<?php

namespace App\Service;

use MongoDB\Client as MongoDB;
use MongoDB\BSON\ObjectId;
use App\Entity\InventoryItem;

class DocumentStorage
{
    /** MongoDB\Client */
    protected $mongo;

    /**
     * Set up
     */
    protected function init()
    {
        if (!$this->mongo) {
            $this->mongo = new MongoDB(getenv('DATABASE_URL'));
        }
    }

    /**
     * Get a reference to our inventory collection
     * 
     * @return MongoDB\Collection
     */
    public function getInventory()
    {
        $this->init();
        return $this->mongo->inventory->inventory;
    }

    /**
     * Get an inventory item
     * 
     * @return App\Entity\InventoryItem
     */
    public function getInventoryItem(string $id) : InventoryItem
    {
        $inventory = $this->getInventory();
        return $inventory->findOne(['_id' => new ObjectId("$id")]);
    }

    /**
     * Persist an inventory item
     * 
     * @return string The ID of the item
     */
    public function saveInventoryItem(InventoryItem $item)
    {
        if (!$item) {
            throw new \RuntimeException('Empty item can not be saved');
        }
        $inventory = $this->getInventory();
        $result = $inventory->replaceOne(
            ['_id' => $item->getObjectId()],
            $item,
            ['upsert' => true]
        );
        return (string) $result->getUpsertedId();
    }

    /**
     * Get tags by type
     * 
     * @param string $type One of TAG_CATEGORY_*
     * @return MongoDB\Collection
     */
    public function getTags($type)
    {
        $this->init();
        $collection = $this->mongo->inventory->tags;
        return $collection->find(['type' => $type]);
    }
}
