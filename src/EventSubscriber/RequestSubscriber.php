<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;
use App\Services\InfoFormatter;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class RequestSubscriber implements EventSubscriberInterface
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                    ['addLogRequest', 112],
            ],
        ];
    }

    public function addLogRequest(RequestEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->logger->info(InfoFormatter::KEYWORD);
        }
    }
}
