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

class InventoryController extends Controller
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
        return $this->render('inventory/list.html.twig', ['items' => $items]);
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

        $form = $this->createFormBuilder($item)
            ->add('name', TextType::class)
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
            if ($request->request->get('submit', 'submit') === 'submit_add') {
                return $this->redirectToRoute('inventory_add');
            } else {
                return $this->redirectToRoute('inventory_list');
            }
        }

        return $this->render(
            'inventory/edit.html.twig', 
            ['form' => $form->createView(), 'mode' => $mode]
        );
    }
}