<?php

namespace SUS\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use FOS\RestBundle\View\View;

class FyController extends Controller {
    /**
     * @Secure("ROLE_USER")
     */
    public function getFysAction() {
        $repo = $this->container->get('doctrine')->getManager()->getRepository('SUS\SiteBundle\Entity\Unit');
        $fys = $repo->findFys(array(
            'name' => $this->getRequest()->get('name')
        ));
        $view = View::create()->setStatusCode(200)->setData($fys);
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}