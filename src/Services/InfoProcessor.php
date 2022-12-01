<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class InfoProcessor
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @return array|void
     */
    public function __invoke(array $record)
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return $record;
        }
        if (!$session->isStarted()) {
            return $record;
        }

        $sessionContent = $session->all();
        // Nettoyage de la session des infos inutiles

        // Infos sur l'utilisateur spotify courant
        $record['extra']['request'] = $this->addRequestInformations();
        $record['extra']['session'] = $sessionContent;

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
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }
       
        return [
            'URI' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'parameters' => $content,
        ];
    }
}

