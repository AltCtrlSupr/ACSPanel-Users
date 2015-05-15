<?php

namespace ACS\ACSPanelUsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use ACS\ACSPanelUsersBundle\Form\FosUserType;
use ACS\ACSPanelUsersBundle\Entity\FosUser;
use ACS\ACSPanelUsersBundle\Event\UserEvents;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
		     $entities = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->findAll();
	     }elseif(true === $this->get('security.context')->isGranted('ROLE_ADMIN')){
		     $entities = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->findBy(array('parent_user' => $this->get('security.context')->getToken()->getUser()->getIdChildIds()));
	     }else{
		     $user = $this->get('security.context')->getToken()->getUser();
		     $entities = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->findBy(array('parent_user' => $user->getId()));
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
        $rep = $em->getRepository('ACSACSPanelUsersBundle:FosUser');

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

        $form = $this->createForm(new FosUserType($this->get('security.context')), $entity, array(
            'em' => $this->getDoctrine()->getEntityManager(),
        ));

        return $this->render('ACSACSPanelUsersBundle:User:new.html.twig', array(
            'search_action' => 'user_search',
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates new user
     *
     * @Route("/create/", name="users_create")
     */
    public function createAction(Request $request)
    {
        $entity  = new FosUser();
        $form = $this->createForm(new FosUserType($this->get('security.context')), $entity, array(
            'em' => $this->getDoctrine()->getEntityManager(),
        ));
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            // Persisting plans
            // @todo: Do this with events
            $postData = $request->request->get('acs_acspanelbundle_fosusertype');
            if(isset($postData['puser'])){
                $plans = $postData['puser'];

                foreach ($plans as $plan) {
                    $assignplan = $em->getRepository('ACSACSPanelBundle:Plan')->find($plan['uplans']);
                    if($assignplan){
                        $new_plan = new UserPlan();
                        $new_plan->setPuser($entity);
                        $new_plan->setUplans($assignplan);
                        $em->persist($new_plan);
                    }
                }

            }

            // Password encode setting
            $userManager = $this->container->get('fos_user.user_manager');
            $entity->setPlainPassword($entity->getPassword());
            $userManager->updatePassword($entity);
            $userManager->updateUser($entity);

            $em->persist($entity);
            $em->flush();

            // $dispatcher = new EventDispatcher();

            // $dispatcher->dispatch(UserEvents::USER_REGISTER, new FilterUserEvent($entity));

            return $this->redirect($this->generateUrl('users_edit', array('id' => $entity->getId())));
        }

        return $this->render('ACSACSPanelUsersBundle:User:new.html.twig', array(
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

        $entity = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->find($id);

        if (!$entity->userCanSee($this->get('security.context'))) {
            throw new \Exception('You cannot edit this entity!');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ACSACSPanelUsersBundle:User:show.html.twig', array(
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
        $user = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->find($id);

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

        $entity = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FosUser entity.');
        }

        $editForm = $this->createForm(new FosUserType($this->get('security.context')), $entity, array(
            'em' => $em,
        ));

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ACSACSPanelUsersBundle:User:edit.html.twig', array(
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
            $entity = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->find($id);

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

    /**
     * Edits an existing FosUser entity.
     *
     * @Route("/update/{id}", name="users_update")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ACSACSPanelUsersBundle:FosUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FosUser entity.');
        }


        $originalPlans = array();

        // Create an array of the current Tag objects in the database
        foreach ($entity->getPuser() as $plan) {
            $originalPlans[] = $plan;
        }


        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new FosUserType($this->get('security.context')), $entity, array(
            'em' => $this->getDoctrine()->getEntityManager(),
        ));

        $editForm->bind($request);

        if ($editForm->isValid()) {
            // filter $originalPlans to contain tags no longer present
            foreach ($entity->getPuser() as $plan) {
                foreach ($originalPlans as $key => $toDel) {
                    if ($toDel->getId() === $plan->getId()) {
                        unset($originalPlans[$key]);
                    }
                }
            }

            // remove the relationship between the tag and the Task
            foreach ($originalPlans as $plan) {
                // if it were a ManyToOne relationship, remove the relationship like this
                $plan->setPuser(null);

                // if you wanted to delete the Tag entirely, you can also do that
                $em->remove($plan);
            }

            $em->persist($entity);
            $em->flush();


            return $this->redirect($this->generateUrl('users_edit', array('id' => $id)));
        }

        return $this->render('ACSACSPanelUsersBundle:User:edit.html.twig', array(
            'search_action' => 'user_search',
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }



    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }




}
