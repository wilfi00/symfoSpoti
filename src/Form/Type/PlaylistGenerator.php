<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PlaylistGenerator extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nbSongs', ChoiceType::class,   [
                'choices' => ['5' => '5', '10' => '10'],
                'label'   => 'Nombre de chansons par groupe : ',])

        ;
    }
}
