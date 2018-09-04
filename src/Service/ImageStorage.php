<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\InventoryItem;

class ImageStorage
{
    /** @var string */
    protected $basePath;

    /**
     * Constructor
     * 
     * @param string $basePath See services.yaml
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getItemImagePath(InventoryItem $item)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $item->getId();
    }

    /**
     * @param InventoryItem $item
     * @param UploadedFile[] $files
     */
    public function saveItemImages(InventoryItem $item, array $files)
    {
        $itemPath = $this->getItemImagePath($item);
        if (!file_exists($itemPath)) {
            mkdir($itemPath);
        }
        $count = 0;
        foreach ($files as $file) {
            if (!$file->isValid()) {
                throw new \RuntimeException($file->getErrorMessage());
            }
            $extension = $file->guessExtension();
            if (!$extension) {
                $extension = 'bin';
            }
            $file->move($itemPath, time() . '_' . $count . '.' . $extension);
            $count++;
            // TODO: Also save a copy scaled to 200px wide
        }
    }

    /**
     * Get image file names associated with an item
     * 
     * @return string[] Array of image file names (excluding path)
     */
    public function getItemImages(InventoryItem $item) : array
    {
        $images = [];
        $path = $this->getItemImagePath($item);
        if (file_exists($path)) {
            $iter = new \DirectoryIterator($path);
            foreach ($iter as $file) {
                if (!$file->isDot()) {
                    $images[] = $file->getFilename();
                }
            }
        }
        
        return $images;
    }

    /**
     * Remove an item's image from storage
     * 
     * @param InventoryItem $item
     * @param string $filename
     */
    public function deleteItemImage(InventoryItem $item, string $filename)
    {
        $path = $this->getItemImagePath($item);
        if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
            unlink($path . DIRECTORY_SEPARATOR);
        }
    }
}
