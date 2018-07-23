<?php

namespace App\Entity;

class Tag extends Persistable
{
    const TAG_CATEGORY_LOCATION = 'location';
    const TAG_CATEGORY_TYPE = 'type';

    /** @var string Tag type, one of TAG_CATEGORY_* */
    protected $type = self::TAG_CATEGORY_TYPE;

    /** @var string */
    protected $name = '';

    /** @var int */
    protected $count = 0;

    public function setType(string $type) 
    {
        $this->type = $type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setName(string $name) 
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function incrementCount()
    {
        $this->count++;
    }

    public function decrementCount()
    {
        $this->count--;
    }

    public function getCount() : int
    {
        return $this->count;
    }
}
