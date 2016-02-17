<?php

namespace WH\UserBundle\Controller\Backend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use APP\UserBundle\Entity\User;
use APP\UserBundle\Form\UserType;


/**
 * @Route("/admin/users")
 * Class UserController
 * @package APP\UserBundle\Controller
 */
class UserController extends Controller
{


    /**
     * @Route("/{page}", name="wh_admin_users", requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @param $page
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');

        $sessName = 'dataUserSearch';

        $data = $session->get($sessName);

        if (!$data) {
            $data = array();
        }

        $form = $this->_returnFormSearch($data);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            $data = $form->getData();

            // set and get session attributes
            $session->set($sessName, $data);

            return $this->redirect($request->headers->get('referer'));

        }

        $max = $request->query->get('max');

        $max = ($max) ? $max : 50;

        $entities = $em->getRepository('APPUserBundle:User')->get(
            'paginate',
            array(
                'limit' => $max,
                'page' => $page,
                'conditions' => $data
            )
        );

        //Lise des rôles
        $roles = array();
        $r = $this->container->getParameter('security.role_hierarchy.roles');
        foreach ($r as $k => $i) {
            $roles[] = $k;
        }

        $pagination = array(
            'page'         => $page,
            'route'        => 'wh_admin_users',
            'pages_count'  => ceil(count($entities) / $max),
            'route_params' => array(),
            'max'          => $max
        );

        //Sortie
        return $this->render(
            'WHUserBundle:Backend:User/index.html.twig',
            array(
                'entities' => $entities,
                'form' => $form->createView(),
                'roles' => $roles,
                'pagination' => $pagination

            )

        );


    }

    private function _returnFormSearch($data)
    {

        $em = $this->getDoctrine()->getEntityManager();

        $form = $this->createFormBuilder($data)
            ->add(
                'Search', 'text', array(
                    'label'     => false,
                    'attr'      => array('placeholder' => 'Chercher'),
                    'required' => false
                )
            )
            ->getForm();


        return $form;

    }



    /**
     * @Route("/create", name="wh_admin_user_create")
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {

        $entity = new User();

        $form = $this->createForm(
            new UserType(array(
                'roles' => $this->container->getParameter('security.role_hierarchy.roles')
            )),
            $entity
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Opération réussie');

            $response = new JsonResponse();

            $response->setData(
                array(
                    'valid' => true,
                    'redirect' => $this->generateUrl('wh_admin_users')
                )
            );

            return $response;


        }

        return $this->render(
            'WHUserBundle:Backend:User/create.html.twig',
            array(
                'form' => $form->createView()

            )
        );


    }


    /**
     * @param $User
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @ParamConverter("User", class="APPUserBundle:User")
     * @Route("/update/{User}", name="wh_admin_user_update")
     */
    public function updateAction($User, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            new UserType(array(
                'roles' => $this->container->getParameter('security.role_hierarchy.roles')
            )),
            $User
        );

        if ($form->handleRequest($request)->isValid()) {


            $em->persist($User);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Opration réussie');

            $response = new JsonResponse();

            $response->setData(
                array(
                    'valid' => true,
                    'redirect' => $this->generateUrl('wh_admin_users')
                )
            );

            return $response;


        }


        return $this->render(
            'WHUserBundle:Backend:User/update.html.twig',
            array(
                'form' => $form->createView(),
                'User' => $User
            )
        );


    }


    /**
     * @Route("/send/{User}", name="wh_admin_user_send_access")
     * @param $User
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @ParamConverter("User", class="APPUserBundle:User")
     */
    public function sendAccessAction($User, Request $request)
    {


        $em = $this->getDoctrine()->getManager();

        $password = $this->get('wh.user.password')->genere(6);
        $User->setPlainPassword($password);
        $User->setEnabled(true);

        $em->persist($User);
        $em->flush();


        if ($this->get('wh.user.notification')->sendAccess($User, $password)) {

            $request->getSession()->getFlashBag()->add('success', 'Accès envoyé');

        } else {

            $request->getSession()->getFlashBag()->add('error', 'Une erreur est survenue');

        }

        return $this->redirect($this->generateUrl('wh_admin_users'));


    }



    /**
     * @Route("/show/{User}", name="wh_admin_user_show")
     * @param $User
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @ParamConverter("User", class="APPUserBundle:User")
     */
    public function showAction($User, Request $request)
    {


        return $this->render(
            'WHUserBundle:User:admin/show.html.twig',
            array(
                'User' => $User,
            )
        );

    }


    /**
     * @Route("/delete/{User}", name="wh_admin_user_delete")
     * @param Request $request
     * @param $User
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @ParamConverter("User", class="APPUserBundle:User")
     */
    public function deleteAction(Request $request, $User)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('wh_admin_user_delete', array('User' => $User->getId())))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em->remove($User);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'User supprimé');

            $response = new JsonResponse();

            $response->setData(
                array(
                    'valid' => true,
                    'redirect' => $this->generateUrl('wh_admin_users')
                )
            );

            return $response;
        }

        return $this->render(
            'WHUserBundle:Backend:User/delete.html.twig',
            array(
                'User' => $User,
                'form' => $form->createView()
            )
        );


    }


}
