<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\InventoryItem;
use App\Service\DocumentStorage;

class Inventory extends Controller
{
    /** @var DocumentStorage */
    protected $docs;

    public function __construct(DocumentStorage $docs)
    {
        $this->docs = $docs;
    }

    public function listItems()
    {
        $items = $this->docs->getInventory()->find();
        // If there are no items, bounce to the add page
        $exists = false;
        foreach ($items as $item) {
            $exists = true;
            break;
        }
        if ($exists) {
            return $this->render('inventory/list.html.twig', ['items' => $items]);
        } else {
            return $this->redirectToRoute('inventory_add');
        }
    }

    public function getItem($id)
    {

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

        $form = $this->createFormBuilder($item)
            ->add('name', TextType::class)
            ->add(
                'purchasePrice', 
                MoneyType::class, 
                ['required' => false]
            )
            ->add(
                'value', 
                MoneyType::class, 
                ['label' => 'Current value (per item)', 'required' => false]
            )
            ->add('quantity', IntegerType::class)
            ->add(
                'notes', 
                TextareaType::class,
                ['required' => false])
            ->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $id = $this->docs->saveInventoryItem($item);
            return $this->redirectToRoute('inventory_get', ['id' => $id]);
        }

        return $this->render(
            'inventory/edit.html.twig', 
            ['form' => $form->createView(), 'mode' => $mode]
        );
    }
}