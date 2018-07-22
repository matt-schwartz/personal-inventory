<?php

namespace App\Service;

use MongoDB\Client as MongoDB;
use App\Entity\InventoryItem;

class DocumentStorage
{
    /** MongoDB\Client */
    protected $mongo;

    /** @var boolean Have we initialized the databases and collections? */
    protected $init = false;

    /**
     * Get a reference to our inventory collection
     * 
     * @return MongoDB\Collection
     */
    public function getInventory()
    {
        if (!$this->mongo) {
            $this->mongo = new MongoDB(getenv('DATABASE_URL'));
        }
        // if (!$this->init) {
        //     // Create db if it doesn't exist
        //     $found = false;
        //     foreach ($this->mongo->listDatabases() as $db) {
        //         if ($db->getName() === 'inventory') {
        //             $found = true;
        //             break;
        //         }
        //     }
        //     if (!$found) {
        //         $this->mongo->createDatabase('inventory');
        //     }

        //     $found = false;
        //     foreach ($this->mongo->inventory->listCollections as $collection) {
        //         if ($collection->getName() === 'inventory') {
        //             $found = true;
        //             break;
        //         }
        //     }
        //     if (!$found) {
        //         $this->mongo->inventory->createCollection('inventory');
        //     }

        //     $this->init = true;
        // }

        // Return collection
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
        return $inventory->findOne(['_id' => MongoDB\BSON\ObjectId("$id")]);
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

}
