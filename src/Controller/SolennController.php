<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SolennController extends AbstractController
{
    /**
     * @Route("/testAreaSolenn", name="solenn")
     */
    public function testAreaSolenn(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('Artiste', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Chercher cet artiste'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();
            print_r($task);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            // return $this->redirectToRoute('task_success');
        }

        return $this->render('testArea/solenn.html.twig', [
           'form' => $form->createView(),
       ]);
    }

}
