<?php

namespace App\Service;

use MongoDB\Client as MongoDB;
use MongoDB\BSON\ObjectId;

use App\Entity\InventoryItem;
use App\Entity\Tag;

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
    protected function getInventoryCollection() : \MongoDB\Collection
    {
        $this->init();
        return $this->mongo->inventory->inventory;
    }

    protected function getTagCollection() : \MongoDB\Collection
    {
        $this->init();
        return $this->mongo->inventory->tags;
    }

    /**
     * Get inventory items with an optional filter
     * 
     * TODO: Sort
     * 
     * @return MongoDB\Driver\Cursor
     */
    public function getInventoryItems($filter = []) : iterable
    {
        $this->getInventoryCollection()->find($filter);
    }

    /**
     * Get an inventory item
     * 
     * @return App\Entity\InventoryItem
     */
    public function getInventoryItem(string $id) : ?InventoryItem
    {
        $inventory = $this->getInventoryCollection();
        return $inventory->findOne(['_id' => new ObjectId("$id")]);
    }

    /**
     * Persist an inventory item
     * 
     * @return string The ID of the item
     */
    public function saveInventoryItem(InventoryItem $item) : string
    {
        if (!$item) {
            throw new \RuntimeException('Empty item can not be saved');
        }
        $inventory = $this->getInventoryCollection();
        $result = $inventory->replaceOne(
            ['_id' => $item->getObjectId()],
            $item,
            ['upsert' => true]
        );
        return (string) $item->getId();
    }

    /**
     * Get tags by type
     * 
     * @param string $category One of CATEGORY_*
     * @return MongoDB\Driver\Cursor
     */
    public function getTags(string $category) : iterable
    {
        $this->init();
        $collection = $this->getTagCollection();
        return $collection->find(['category' => $category]);
    }

    /**
     * Get a Tag entity by name
     */
    public function getTagByName(string $category, string $name) : ?Tag
    {
        $this->init();
        $collection = $this->getTagCollection();
        // TODO: Make the search case insensitive
        return $collection->findOne(['category' => $category, 'name' => $name]);
    }

    /**
     * Persist tags
     * 
     * @param string $category One of CATEGORY_*
     * @param array $tags Array of strings or Tag entities
     */
    public function saveTags(string $category, array $tags)
    {
        $collection = $this->getTagCollection();
        foreach ($tags as $tag) {
            if ($tag instanceof Tag) {
                $tagEntity = $tag;
            } else {
                $tagEntity = $this->getTagByName($category, $tag);
                if (!$tagEntity) {
                    $tagEntity = new Tag();
                    $tagEntity->setName($tag);
                    $tagEntity->setCategory($category);
                }
            }
            // TODO: Calculate each tag's item count
            $result = $collection->replaceOne(
                ['_id' => $tagEntity->getObjectId()],
                $tagEntity,
                ['upsert' => true]
            );
        }
    }
}
