<?php
namespace WH\UserBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */
class ChangePasswordListener implements EventSubscriberInterface {
    private $router;

    public function __construct(UrlGeneratorInterface $router) {
        $this->router = $router;
    }

    public static function getSubscribedEvents() {
        return array(
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChangeSuccess',
        );
    }

    public function onPasswordChangeSuccess(FormEvent $event) {
        $url = $this->router->generate('wh_membre_home');
        $event->setResponse(new RedirectResponse($url));
    }
}