<?php

namespace Tactics\TableBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('TacticsTableBundle:Default:index.html.twig', array('name' => $name));
    }
}
