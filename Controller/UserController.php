<?php

namespace ACS\ACSPanelUsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class UserController extends Controller
{
    /**
     * @Route("/", name="users")
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

    /**
     * @Route("/search", name="user_search")
     * @Template()
     */
    public function searchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('ACSACSPanelBundle:FosUser');

        $term = $request->request->get('term');

        $query = $rep->createQueryBuilder('u')
            ->where('u.id = ?1')
            ->orWhere('u.username LIKE ?1')
            ->orWhere('u.email LIKE ?1')
            ->orWhere('u.roles LIKE ?1')
            ->orWhere('u.firstname LIKE ?1')
            ->orWhere('u.lastname LIKE ?1')
            ->orWhere('u.uid = ?1')
            ->orWhere('u.gid = ?1')
            ->setParameter('1',$term)
            ->getQuery();

        $entities = $query->execute();

        return $this->render('ACSACSPanelUserBundle:User:index.html.twig', array(
            'search_action' => 'user_search',
            'term' => $term,
            'entities' => $entities,
        ));

    }

    /**
     * Displays a form to create a new FosUser entity.
     *
     * @Route("/", name="users_new")
     */
    public function newAction()
    {
        $entity = new FosUser();

        $form = $this->createForm(new FosUserType(), $entity, array(
            'em' => $this->getDoctrine()->getEntityManager(),
        ));

        return $this->render('ACSACSPanelUserBundle:FosUser:new.html.twig', array(
            'search_action' => 'user_search',
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }


}
