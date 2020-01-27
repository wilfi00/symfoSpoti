<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PlaylistSelection extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $requestSpoti  = \App\SpotiImplementation\Request::factory();
        $playlists     = $requestSpoti->getUserPlaylistsForModaleSelection();

        $builder
            ->add('nbSongs', ChoiceType::class,   [
                'choices' => ['5' => '5', '10' => '10'],
                'label'   => 'Nombre de chansons par groupe : ',])
            ->add('playlist', ChoiceType::class, [
                'choices' => $playlists,
                'label'   => 'Ajouter les chansons dans cette playlist : ',])
        ;
    }
}
