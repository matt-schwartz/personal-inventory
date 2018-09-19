<?php

namespace App\Service;

use Gumlet\ImageResize;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\InventoryItem;

class ImageStorage
{
    const WIDTH_SMALL = 200;

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
        $time = time();
        $count = 0;
        foreach ($files as $file) {
            if (!$file->isValid()) {
                throw new \RuntimeException($file->getErrorMessage());
            }
            $extension = $file->guessExtension();
            if (!$extension) {
                $extension = 'bin';
            }
            $originalFilename = $time . 'i' . $count . '.' . $extension;
            $file->move($itemPath, $originalFilename);

            $resizer = new ImageResize($itemPath . DIRECTORY_SEPARATOR . $originalFilename);
            $resizer->resizeToWidth(self::WIDTH_SMALL);
            $resizer->save(
                $itemPath . DIRECTORY_SEPARATOR . $time . 'i' . $count . 'w' . self::WIDTH_SMALL . '.' . $extension
            );
            $count++;
        }
    }

    /**
     * Get image file names associated with an item
     * 
     * @param InventoryItem $item
     * @param int $width One of WIDTH_* (optional)
     * @return string[] Array of image file names (excluding path)
     */
    public function getItemImages(InventoryItem $item, integer $width = null) : array
    {
        $images = [];
        $path = $this->getItemImagePath($item);
        if (file_exists($path)) {
            $iter = new \DirectoryIterator($path);
            foreach ($iter as $file) {
                if (!$file->isDot()) {
                    $name = $file->getFilename();
                    if ($width) {
                        if (strpos($name, 'w' . $width) !== false) {
                            $images[] = $name;
                        }
                    } else {
                        $nameParts = explode('.', $name);
                        if (strpos($nameParts[0], 'w') === false) {
                            $images[] = $name;
                        }
                    }
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
        $files = [$filename];
        $files[] = str_replace('.', 'w' . self::WIDTH_SMALL . '.', $filename);
        foreach ($files as $filename) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                unlink($path . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }
}
