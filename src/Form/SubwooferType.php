<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Entity\Subwoofer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubwooferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Un champ de formulaire par attribut de l'entité Subwoofer
        $builder
            ->add('model', null, ['label' => 'Modèle'])
            ->add('brand', null, ['label' => 'Marque'])
            ->add('year', null, ['label' => 'Année'])
            // Relation ManyToOne : liste déroulante des inventaires existants
            ->add('inventory', EntityType::class, [
                'class' => Inventory::class,
                'choice_label' => 'description',
                'label' => 'Collection',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subwoofer::class,
        ]);
    }
}
