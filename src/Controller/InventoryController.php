<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\InventoryItem;
use App\Entity\Tag;
use App\Service\DocumentStorage;
use App\Service\TagChoiceLoader;

class InventoryController extends Controller
{
    /** @var DocumentStorage */
    protected $docs;

    public function __construct(DocumentStorage $docs)
    {
        $this->docs = $docs;
    }

    public function listItems(Request $request, string $category = null, string $tag = null)
    {
        if ($category && $tag) {
            $items = $this->docs->getInventoryItemsByTag($category, $tag);
        } else {
            $items = $this->docs->getInventoryItems();
        }
        return $this->render(
            'inventory/list.html.twig', 
            [
                'items' => $items,
                'tag' => $tag
            ]
        );
    }

    public function getItem($id)
    {
        $item = $this->docs->getInventoryItem($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }
        return $this->render(
            'inventory/view.html.twig', 
            ['item' => $item]
        );
    }

    public function editItem(Request $request, $id = null)
    {
        if ($id) {
            $item = $this->docs->getInventoryItem($id);
            if (!$item) {
                throw $this->createNotFoundException('Item not found');
            }
            $mode = 'edit';
        } else {
            $item = new InventoryItem();
            $mode = 'new';
        }

        $tagAttributes = [
            'attr' => ['class' => 'tags'],
            'expanded' => false,
            'help' => 'Hit enter or comma to create new tags',
            'multiple' => true,
            'required' => false
        ];

        $form = $this->createFormBuilder($item)
            ->add('name', TextType::class)
            ->add('quantity', IntegerType::class)
            ->add(
                'purchasePrice', 
                MoneyType::class, 
                ['label' => 'Purchase price (per item)', 'required' => false]
            )
            ->add(
                'value', 
                MoneyType::class, 
                ['label' => 'Current value (per item)', 'required' => false]
            )
            ->add(
                'types',
                ChoiceType::class,
                [
                    'label' => 'Type / Tags',
                    'choices' => $this->getTags($request, 'types', Tag::CATEGORY_ITEM_TYPE),
                ] + $tagAttributes
            )
            ->add(
                'locations',
                ChoiceType::class,
                [
                    'label' => 'Location(s)',
                    'choices' => $this->getTags($request, 'locations', Tag::CATEGORY_ITEM_LOCATION),
                ] + $tagAttributes
            )
            ->add(
                'notes', 
                TextareaType::class,
                ['required' => false])
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $id = $this->docs->saveInventoryItem($item);
            if ($request->request->get('submit', 'submit') === 'submit_add') {
                return $this->redirectToRoute('inventory_add');
            } elseif ($request->query->get('return_to', '') === 'list') {
                return $this->redirectToRoute('inventory_list');
            } else {
                return $this->redirectToRoute('inventory_get', ['id' => $id]);
            }
        }

        return $this->render(
            'inventory/edit.html.twig', 
            ['form' => $form->createView(), 'mode' => $mode]
        );
    }

    /**
     * Get tags, including any new tags POSTed through the form
     * 
     * @param Request $request HTTP request
     * @param string $field Form and entity field name
     * @param string $tagCategory
     * @return string[]
     */
    private function getTags(Request $request, $field, $tagCategory)
    {
        $tags = [];
        if ($request->getMethod() === 'POST') {
            $formInput = $request->request->get('form');
            if (array_key_exists($field, $formInput)) {
                $tags = array_combine($formInput[$field], $formInput[$field]);
            }
        }
        foreach ($this->docs->getTags($tagCategory) as $tag) {
            $tags[(string) $tag] = (string) $tag;
        }
        return $tags;
    }
}
