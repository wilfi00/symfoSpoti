<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use \App\SpotiImplementation\Auth as SpotiAuth;
use \App\Services\Utils;

class InfoProcessor
{
    private $session;
    private $requestStack;
    
    public function __construct(SessionInterface $session, RequestStack $requestStack)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
    }
    
    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $this->session->start();
        if ($this->session->isStarted()) {
            $sessionContent = $this->session->all();
            // Nettoyage de la session des infos inutiles
            unset($sessionContent[SpotiAuth::SESSION_BASICAPISESSION]);
            unset($sessionContent[SpotiAuth::SESSION_APISESSION]);
            unset($sessionContent[SpotiAuth::SESSION_INFORMATION]);
            
            // Infos sur l'utilisateur spotify courant
            $record['extra']['spotiUserInformation'] = unserialize($this->session->get(SpotiAuth::SESSION_INFORMATION));
            $record['extra']['request'] = $this->addRequestInformations();
            $record['extra']['session'] = $sessionContent;
        }
        
        return $record;
    }
    
    protected function addRequestInformations()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (empty($request)) {
            return;
        }
        
        $content = $request->getContent();
        if (Utils::isJson($content)) {
            $content = json_decode($content, true);
        }
       
        return [
            'URI' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'parameters' => $content,
        ];
    }
}

