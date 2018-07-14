<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Home extends Controller
{
    public function index()
    {
        return $this->redirectToRoute('inventory_list');
    }
}
