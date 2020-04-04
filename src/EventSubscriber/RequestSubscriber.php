<?php

namespace App\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class RequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                    ['localeRequest', 112],
            ],
        ];
    }

    public function localeRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $request->setLocale($request->getPreferredLanguage());
    }
}
