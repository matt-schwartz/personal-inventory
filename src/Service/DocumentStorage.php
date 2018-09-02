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
     * Get inventory items
     * 
     * @return MongoDB\Driver\Cursor
     */
    public function getInventoryItems() : iterable
    {
        // TODO: Sort
        return $this->getInventoryCollection()->find();
    }

    /**
     * Get inventory items by tag name
     * 
     * @param string $category One of Tag::CATEGORY_*
     * @param string $tag Tag name
     * @return MongoDB\Driver\Cursor
     */
    public function getInventoryItemsByTag(string $category, string $tag) : iterable
    {
        // TODO: Sort
        return $this->getInventoryCollection()->find([
            $category => [
                '$regex' => '^' . $tag . '$',
                '$options' => 'i'
            ]
        ]);
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
        // Get the original tags so we can update their counters
        $originalItem = $this->getInventoryItem($item->getId());
        $originalTypes = [];
        $originalLocations = [];
        if ($originalItem) {
            $originalTypes = $originalItem->getTypes();
            $originalLocations = $originalItem->getLocations();
        }
        $inventory->replaceOne(
            ['_id' => $item->getObjectId()],
            $item,
            ['upsert' => true]
        );
        $this->saveInventoryItemTags(Tag::CATEGORY_ITEM_TYPE, $originalTypes, $item->getTypes());
        $this->saveInventoryItemTags(Tag::CATEGORY_ITEM_LOCATION, $originalLocations, $item->getLocations());
    
        return (string) $item->getId();
    }

    /**
     * Save tag entities associated with an inventory item being updated
     * 
     * @param string $category One of Tag::CATEGORY_*
     * @param string[] $originalTagStrings Tag strings associated with the item before update
     * @param string[] $updatedTagStrings Tag strings associated with the updated item
     */
    protected function saveInventoryItemTags(string $category, array $originalTagStrings, array $updatedTagStrings)
    {
        $tags = [];
        foreach (array_diff($originalTagStrings, $updatedTagStrings) as $removed) {
            if ($tag = $this->getTagByName($category, $removed)) {
                $tag->decrementCount();
                $tags[] = $tag;
            }
        }
        foreach (array_diff($updatedTagStrings, $originalTagStrings) as $added) {
            $tag = $this->getTagByName($category, $added);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($added);
                $tag->setCategory($category);
            }
            $tag->incrementCount();
            $tags[] = $tag;
        }
        $collection = $this->getTagCollection();
        foreach ($tags as $tag) {
            $collection->replaceOne(
                ['_id' => $tag->getObjectId()],
                $tag,
                ['upsert' => true]
            );
        }
    }

    /**
     * Get tags, optionally by category
     * 
     * @param string $category One of Tag::CATEGORY_*
     * @return MongoDB\Driver\Cursor
     */
    public function getTags(string $category = null) : iterable
    {
        $this->init();
        $collection = $this->getTagCollection();
        $filter = [];
        if ($category) {
            $filter = ['category' => $category];
        }
        return $collection->find($filter);
    }

    /**
     * Get "top" 5 tags by category
     * 
     * @param string $category One of Tag::CATEGORY_*
     * @return MongoDB\Driver\Cursor
     */
    public function getTopTags(string $category) : iterable
    {
        $this->init();
        $collection = $this->getTagCollection();
        return $collection->find(
            ['category' => $category],
            ['limit' => 5, 'sort' => ['count' => -1]]
        );
    }

    /**
     * Get "top" 5 type tags
     * 
     * @return MongoDB\Driver\Cursor
     */
    public function getTopTypeTags() : iterable
    {
        return $this->getTopTags(Tag::CATEGORY_ITEM_TYPE);
    }

    /**
     * Get "top" 5 location tags
     * 
     * @return MongoDB\Driver\Cursor
     */
    public function getTopLocationTags() : iterable
    {
        return $this->getTopTags(Tag::CATEGORY_ITEM_LOCATION);
    }

    /**
     * Get a Tag entity by name
     * 
     * @param string $category One of Tag::CATEGORY_*
     * @param string $name
     * @return Tag|null
     */
    public function getTagByName(string $category, string $name) : ?Tag
    {
        $this->init();
        $collection = $this->getTagCollection();
        return $collection->findOne(
            [
                'category' => $category, 
                // Case insensitive indexed search
                'name' => [
                    '$regex' => '^' . $name . '$',
                    '$options' => 'i'
                ]
            ]
        );
    }
}
