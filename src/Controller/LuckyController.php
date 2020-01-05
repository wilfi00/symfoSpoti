<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{
    /**
     * @Route("/lucky/number")
     */
    public function number()
    {
        dump('test');
        $number = random_int(0, 100);
        // qsdf
        // return new Response(
        //     '<html><body>Lucky number: '.$number.'</body></html>'
        // );
        // dump($this->render('base.html.twig'));
        // return $this->render('base.html.twig');
        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
