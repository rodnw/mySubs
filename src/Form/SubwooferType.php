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
        ;
        // Le champ de la collection n'est affiché que si elle n'est pas déjà fixée
        if ($options['display_inventory']) {
            // Relation ManyToOne : liste déroulante des inventaires existants
            $builder->add('inventory', EntityType::class, [
                'class' => Inventory::class,
                'choice_label' => 'description',
                'label' => 'Collection',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subwoofer::class,
            'display_inventory' => true,
        ]);
        $resolver->setAllowedTypes('display_inventory', 'bool');
    }
}
