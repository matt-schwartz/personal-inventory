<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        return $this->render('inventory/list.html.twig', ['items' => [$items]]);
    }

    public function getItem($id)
    {

    }

    public function editItem($id = null)
    {
        if ($id) {
            $item = $this->docs->getInventoryItem($id);
        } else {
            $item = new InventoryItem();
        }

        $form = $this->createFormBuilder($item)
            ->add('name', TextType::class)
            ->add('purchasePrice', MoneyType::class)
            ->add('value', MoneyType::class, ['label' => 'Current value (per item)'])
            ->add('quantity', IntegerType::class)
            ->add('notes', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        return $this->render('inventory/edit.html.twig', ['form' => [$form->createView]]);
    }
}