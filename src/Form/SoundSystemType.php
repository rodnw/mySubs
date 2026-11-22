<?php

namespace App\Form;

use App\Entity\SoundSystem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoundSystemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', null, ['label' => 'Description'])
            ->add('subwoofers', CollectionType::class, [
                'entry_type' => SubwooferType::class,
                'entry_options' => [
                    'label' => false,
                    'display_sound_system' => false,
                ],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SoundSystem::class,
        ]);
    }
}
