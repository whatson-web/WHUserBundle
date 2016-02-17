<?php

namespace WH\UserBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @Route("/admin")
 */
class DashboardController extends Controller
{

    /**
     * @Route("/", name="admin_home")
     */
    public function indexAction()
    {
        return $this->render('WHUserBundle:Backend:Dashboard/index.html.twig');
    }



}
