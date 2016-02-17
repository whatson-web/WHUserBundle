<?php

namespace WH\UserBundle\Controller\Backend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APP\UserBundle\Entity\User;
use APP\UserBundle\Form\UserCreateType;
use APP\UserBundle\Form\UserUpdateType;


/**
 * @Route("/admin/users")
 * Class UserController
 * @package APP\UserBundle\Controller
 */
class UserController extends Controller
{

    /**
     * @Route("/", name="admin_users")
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $data = array();

        $formSearch = $this->createForm(new UserSearchType());

        $formSearch->handleRequest($request);

        if ($formSearch->isValid()) $data = $formSearch->getData();

        //Liste des users
        $entities = $em->getRepository('WHUserBundle:User')->getAll(1, 100, $data);


        //Lise des rôles
        $roles = array();
        $r = $this->container->getParameter('security.role_hierarchy.roles');
        foreach($r as $k => $i) $roles[] = $k;


        //Sortie
        return $this->render('WHUserBundle:User:admin/index.html.twig',
            array(
                'entities'      => $entities,
                'formSearch'    => $formSearch->createView(),
                'roles'         => $roles

            )

        );


    }

    /**
     * @Route("/create", name="admin_users_create")
     * @param $type
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction($type, Request $request) {

        $entity = new User();

        $form = $this->createForm(
            new UserCreateType(array(
                'roles' => $this->container->getParameter('security.role_hierarchy.roles')
            )),
            $entity
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Membre créée');

            $response = new JsonResponse();

            $url = ($this->get('request')->request->get('submited') == 'edit') ? $this->generateUrl('whad_user_update', array('id' => $entity->getId())) : $this->generateUrl('whad_user');

            $response->setData(array(
                    'valid'     => true,
                    'redirect'  => $url,
                    'User'      => array(
                        'id'        => $entity->getId(),
                        'username'  => $entity->getName()
                    )
                ));

            return $response;


        }

        return $this->render('WHUserBundle:User:admin/create.html.twig', array(
                'formCreate'   => $form->createView(),
                'type' => $type,

            ));



    }

    /**
     * @Route("/update/{User}", name="admin_user_update")
     * @param $User
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateAction($User, Request $request) {

        $em = $this->getDoctrine()->getManager();



        if (!$entity) throw $this->createNotFoundException('Ce user est inconnue.');


        $form = $this->createForm(
            new UserEditType(array(
                'roles' => $this->container->getParameter('security.role_hierarchy.roles')
            )),
            $entity
        );

        if ($form->handleRequest($request)->isValid()) {


            $url = ($this->get('request')->request->get('submited') == 'edit') ? $this->generateUrl('admin_user_update', array('id' => $entity->getId())) : $this->generateUrl('whad_user');

            $em->flush();

            //Supprimer les contacts vides
            $contacts = $em->getRepository('WHOrganisationBundle:Contact')->findBy(array('value' => ''));

            foreach($contacts as $a) $em->remove($a);

            //Supprimer les adresses vides
            $adress = $em->getRepository('WHOrganisationBundle:Adress')->findBy(array('name' => ''));

            foreach($adress as $a) $em->remove($a);

            $em->flush();

            //Fin de traitement
            $request->getSession()->getFlashBag()->add('success', 'Membre modifié');

            return $this->redirect($this->generateUrl('admin_user_update', array('id' => $id)));
        }


        return $this->render('WHUserBundle:User:admin/update.html.twig', array(
                'formCreate'   => $form->createView(),
                'entity' => $entity
            ));


    }


    /**
     * @Route("/show/{User}", name="admin_user_show")
     * @param $User
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction ($User, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WHUserBundle:User')->find($id);


        return $this->render('WHUserBundle:User:admin/show.html.twig', array(
                'entity' => $entity,
            ));

    }


    /**
     * SUPPRESSION
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WHUserBundle:User')->find($id);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('whad_user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WHUserBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'User supprimée');

            return $this->redirect($this->generateUrl('whad_user'));

        }

        return $this->render('WHUserBundle:User:admin/delete.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));


    }



    public function sendAccessAction($id, Request $request) {


        $em = $this->getDoctrine()->getManager();

        $userManager = $this->container->get('fos_user.user_manager');

        $entity = $em->getRepository('WHUserBundle:User')->find($id);

        if (!$entity) throw $this->createNotFoundException('Unable to find User entity.');

        $password = $this->get('wh.user.password')->genere(6);
        $entity->setPlainPassword($password);
        $entity->setEnabled(true);

        $userManager->updateUser($entity);


        if($this->get('wh.user.notification')->sendAccess($entity, $password)) {

            $request->getSession()->getFlashBag()->add('success', $this->get('translator')->trans('Admin.CompteEnvoye.succes'));

        }else{

            $request->getSession()->getFlashBag()->add('error', $this->get('translator')->trans('Core.error'));

        }

        return $this->redirect($this->generateUrl('whad_user'));



    }


}
