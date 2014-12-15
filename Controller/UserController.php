<?php

namespace ACS\ACSPanelUsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ACS\ACSPanelUsersBundle\Form\FosUserType;

use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/new/", name="users_new")
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

    /**
     * Displays show FosUser entity.
     *
     * @Route("/show/{id}", name="users_show")
     */

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ACSACSPanelBundle:FosUser')->find($id);

        if (!$entity->userCanSee($this->get('security.context'))) {
            throw new \Exception('You cannot edit this entity!');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ACSACSPanelBundle:FosUser:show.html.twig', array(
            'search_action' => 'user_search',
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));

    }

    /**
     * Switch the session to other user to admin purposes
     * @Route("/switch/{id}", name="users_switch")
     */
    public function switchAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $curr_user = $this->get('security.context')->getToken()->getUser();
        $user = $em->getRepository('ACSACSPanelBundle:FosUser')->find($id);

        if (true === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') || $curr_user == $user->getParentUser()) {

            $loginmanager = $this->get('fos_user.security.login_manager');
            $loginmanager->loginUser('main', $user, new Response());

            //$this->get('session')->set('is_superior_user','true');

            return $this->redirect($this->generateUrl('acs_acspanel_homepage'));
        }else{
            throw $this->createNotFoundException('You cannot do this');
        }

    }

    /**
     * Users edit
     * @Route("/edit/{id}", name="users_edit")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ACSACSPanelBundle:FosUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FosUser entity.');
        }

        $editForm = $this->createForm(new FosUserType(), $entity, array(
            'em' => $this->getDoctrine()->getEntityManager(),
        ));

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ACSACSPanelBundle:FosUser:edit.html.twig', array(
            'search_action' => 'user_search',
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a FosUser entity.
     *
     * @Route("/delete/{id}", name="users_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ACSACSPanelBundle:FosUser')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FosUser entity.');
            }

            $userplans = $em->getRepository('ACSACSPanelBundle:UserPlan')->findByPuser($entity);
            foreach ($userplans as $uplan) {
                 $em->remove($uplan);
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('users'));
    }


    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }


}
