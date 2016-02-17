<?php
namespace WH\UserBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class Notification
{


    protected $container;
    protected $translator;
    protected $from;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->translator = $this->container->get('translator');

        $this->from = array($this->container->getParameter('mailer_from.email') => $this->container->getParameter('mailer_from.name'));

    }


    /**
     * Message de bienvenu
     * @param $entity
     */
    public function welcom ($entity) {


        $templating = $this->container->get('templating');
        $mailer = $this->container->get('mailer');

        /*
         * Création du message
         */
        $body = $templating->render(
            'WHMainBundle:Email:welcom.html.twig',
            array(
                'entity' => $entity
            )
        );

        /*
         * Envoie du message
         */
        $message = \Swift_Message::newInstance()
            ->setSubject('Bienvenu')
            ->setFrom($this->from)
            ->setTo($entity->getEmail())
            ->setBody($body)
            ->setContentType('text/html')
        ;

        if($mailer->send($message)) {



        }else{


        }



    }



    public function sendAccess($entity, $password) {


        $templating = $this->container->get('templating');
        $mailer = $this->container->get('mailer');

        /*
         * Création du message
         */
        $body = $templating->render(
            'WHUserBundle:Email:acces.html.twig',
            array(
                'entity' => $entity,
                'password' => $password
            )
        );

        /*
         * Envoie du message
         */
        $message = \Swift_Message::newInstance()
            ->setSubject('Nouveau mot de passe')
            ->setFrom($this->from)
            ->setTo($entity->getEmail())
            ->setBody($body)
            ->setContentType('text/html')
        ;

        return $mailer->send($message);


    }


}
