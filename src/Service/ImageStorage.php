<?php

namespace App\Service;

use Gumlet\ImageResize;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Entity\InventoryItem;

class ImageStorage
{
    const WIDTH_SMALL = 200;
    const HEIGHT_SMALL = 200;

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
     * Save images and their resized versions during upload.
     * 
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
            $this->resizeToWidth($item, $originalFilename, self::WIDTH_SMALL);
            $this->resizeToWidthAndHeight($item, $originalFilename, self::WIDTH_SMALL, self::HEIGHT_SMALL);
            $count++;
        }
    }

    /**
     * Get image file names associated with an item. This returns only the unscaled files.
     * 
     * @param InventoryItem $item
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
                    $name = $file->getFilename();
                    $nameParts = explode('.', $name);
                    if (strpos($nameParts[0], 'w') === false) {
                        $images[] = $name;
                    }
                }
            }
        }
        
        return $images;
    }

    /**
     * Get the full path to an item image file. Generate scaled image as needed.
     * 
     * @param InventoryItem $item
     * @param string $filename The file name of the unscaled image
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function getFilePath(InventoryItem $item, string $filename, int $width = null, int $height = null)
    {
        $unscaledFilename = $filename;
        if ($width && $height) {
            $filename = $this->getFilenameWidthHeight($unscaledFilename, $width, $height);
        } elseif ($width) {
            $filename = $this->getFilenameWidth($filename, $width);
        }
        $path = $this->getItemImagePath($item) . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($path)) {
            if ($width && $height) {
                $this->resizeToWidthAndHeight($item, $unscaledFilename, $width, $height);
            } elseif ($width) {
                $this->resizeToWidth($item, $unscaledFilename, $width);
            } else {
                return '';
            }
        }
        return $path;
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
        // Also delete any scaled images
        $files[] = $this->getFilenameWidth($filename, self::WIDTH_SMALL);
        foreach ($files as $filename) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                unlink($path . DIRECTORY_SEPARATOR . $filename);
            }
        }
    }

    /**
     * Resize an unscaled image to a width.
     * 
     * @param InventoryItem $item
     * @param string $filename
     * @param int $width
     */
    protected function resizeToWidth(InventoryItem $item, string $filename, int $width)
    {
        $itemPath = $this->getItemImagePath($item);
        $resizer = new ImageResize($itemPath . DIRECTORY_SEPARATOR . $filename);
        $resizer->resizeToWidth($width, true);
        $resizer->save(
            $itemPath . DIRECTORY_SEPARATOR . $this->getFilenameWidth($filename, $width)
        );
    }

    /**
     * Resize an unscaled image to a width and height. Image will be cropped to fit in the box.
     * 
     * @param InventoryItem $item
     * @param string $filename
     * @param int $width
     * @param int $height
     */
    protected function resizeToWidthAndHeight(InventoryItem $item, string $filename, int $width, int $height)
    {
        $itemPath = $this->getItemImagePath($item);
        $resizer = new ImageResize($itemPath . DIRECTORY_SEPARATOR . $filename);
        $resizer->crop($width, $height);
        $resizer->save(
            $itemPath . DIRECTORY_SEPARATOR . $this->getFilenameWidthHeight($filename, $width, $height)
        );
    }

    /**
     * Get a filename for a width based on the original filename
     */
    protected function getFilenameWidth(string $filename, int $width)
    {
        $fileparts = explode('.', $filename);
        return $fileparts[0] . 'w' . $width . '.' . $fileparts[1];
    }

    /**
     * Get a filename for a width and height based on the original filename
     */
    protected function getFilenameWidthHeight(string $filename, int $width, int $height)
    {
        $fileparts = explode('.', $filename);
        return $fileparts[0] . 'w' . $width . 'h' . $height . '.' . $fileparts[1];
    }
}
