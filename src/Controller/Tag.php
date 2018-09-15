<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use App\Service\DocumentStorage;

class Tag extends Controller
{
    /** @var DocumentStorage */
    protected $docs;

    public function __construct(DocumentStorage $docs)
    {
        $this->docs = $docs;
    }

    /**
     * Render list of tags from a category
     * 
     * @param string $category One of Tag::CATEGORY_*
     */
    public function listTags(string $category)
    {
        $tags = $this->docs->getTags($category, ['count', 'name']);
        return $this->render('tag/list.html.twig', ['tags' => $tags]);
    }
}
