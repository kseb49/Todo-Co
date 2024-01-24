<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{


    #[Route('/', name:"homepage")]
    /**
     * Display the homepage
     *
     * @return void
     */
    public function index()
    {
        return $this->render('default/index.html.twig');

    }


}
