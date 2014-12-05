<?php

namespace ACS\ACSPanelUsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class UserController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
	     $em = $this->getDoctrine()->getManager();
	     if (true === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
		     $entities = $em->getRepository('ACSACSPanelBundle:FosUser')->findAll();
	     }elseif(true === $this->get('security.context')->isGranted('ROLE_ADMIN')){
		     $entities = $em->getRepository('ACSACSPanelBundle:FosUser')->findBy(array('parent_user' => $this->get('security.context')->getToken()->getUser()->getIdChildIds()));
	     }else{
		     $user = $this->get('security.context')->getToken()->getUser();
		     $entities = $em->getRepository('ACSACSPanelBundle:FosUser')->findBy(array('parent_user' => $user->getId()));
	     }
	     $paginator = $this->get('knp_paginator');
	     $entities = $paginator->paginate(
		     $entities,
		     $this->get('request')->query->get('page', 1)/*page number*/
	     );

	     return array(
		     'search_action' => 'user_search',
		     'entities' => $entities,
	     );

    }

}
