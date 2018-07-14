<?php

namespace App\Service;

use App\Entity\InventoryItem;

class DocumentStorage
{
    /** MongoDB\Client */
    protected $client;

    public function __consruct()
    {
        $this->client = new MongoDB\Client($_ENV['DATABASE_URL']);
    }

    /**
     * Get a reference to our inventory collection
     * 
     * @return MongoDB\Collection
     */
    public function getInventory()
    {
        // Create db if it doesn't exist
        $this->client->inventory;
        // Return collection
        return $this->client->inventory->inventory;
    }

    /**
     * Get an inventory item
     * 
     * @return App\Entity\InventoryItem
     */
    public function getInventoryItem(string $id) : InventoryItem
    {
        return $this->client->inventory->inventory->findOne(['_id' => MongoDB\BSON\ObjectId("$id")]);
    }

    /**
     * Persist an inventory item
     * 
     * @return string The ID of the item
     */
    public function saveInventoryItem(InventoryItem $item)
    {
        $result = $this->client->inventory->inventory->updateOne(
            ['_id' => $item->getId()],
            $item,
            ['upsert' => true]
        );
        return (string) $result->getUpsertedId();
    }

}
