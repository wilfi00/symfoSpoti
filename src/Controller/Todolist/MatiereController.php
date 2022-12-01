<?php

namespace App\Controller\Todolist;

use App\Entity\Todolist\Matiere;
use App\Manager\MatiereManager;
use LogicException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

class MatiereController extends AbstractController
{
    #[Route('/managematiere', name: 'manageMatiere', host: 'home.discovernewmusic.fr')]
    public function manageMatiere(Security $security, Request $request, MatiereManager $matiereManager): Response
    {
        if (!$security->isGranted('ROLE_SPOTIFY')) {
            return $this->redirectToRoute('todolist_not_connected');
        }

        if ($request->isMethod('post')) {
            $mode = $request->get('mode');
            if ($mode === 'add') {
                $matiereManager->add(
                    $this->getUser(),
                    $request->get('name'),
                    $request->get('couleur')
                );
            } elseif ($mode === 'edit') {
                $matiereManager->edit(
                    $matiereManager->find($request->get('matiere')),
                    $this->getUser(),
                    $request->get('name'),
                    $request->get('couleur')
                );
            } elseif ($mode === 'delete') {
                $matiereManager->deleteMatiere(
                    $matiereManager->find($request->get('matiere')),
                    $this->getUser()
                );
            } else {
                throw new LogicException(sprintf("Méthode pas implémentée : %s", $mode));
            }
        }
        return $this->render('todolist/manage_matiere.html.twig', [
            'matieres' => $matiereManager->findForDisplay($this->getUser()),
        ]);
    }

    #[Route('/getDataForMatiere/{id}', name: 'getDataForMatiere', host: 'home.discovernewmusic.fr')]
    public function getDataForMatiere(Matiere $matiere): JsonResponse
    {
        return $this->json([
            'nom' => $matiere->getName(),
            'couleur' => $matiere->getCouleur(),
        ]);
    }

    #[Route('/manualAddMatiere', name: 'manualAddMatiere', host: 'home.discovernewmusic.fr')]
    public function manualAddMatiere(MatiereManager $matiereManager)
    {
//        $matiere = new Matiere();
//        $matiere->setName("Classi");
//        $matiere->setCouleur("orange");
//        $matiereManager->save($matiere);
//
//        $matiere = new Matiere();
//        $matiere->setName("OPPS2");
//        $matiere->setCouleur("blue");
//        $matiereManager->save($matiere);
//
//        $matiere = new Matiere();
//        $matiere->setName("Angl");
//        $matiere->setCouleur("yellow");
//        $matiereManager->save($matiere);
//
//        $matiere = new Matiere();
//        $matiere->setName("MND");
//        $matiere->setCouleur("green");
//        $matiereManager->save($matiere);
//
//        $matiere = new Matiere();
//        $matiere->setName("MNP");
//        $matiere->setCouleur("red");
//        $matiereManager->save($matiere);
//
//        $matiere = new Matiere();
//        $matiere->setName("Intro R");
//        $matiere->setCouleur("pink");
//        $matiereManager->save($matiere);
    }
}
