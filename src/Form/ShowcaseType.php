<?php

namespace App\Form;

use App\Entity\Showcase;
use App\Entity\SoundSystem;
use App\Entity\Subwoofer;
use App\Repository\SubwooferRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShowcaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SoundSystem $soundSystem */
        $soundSystem = $options['sound_system'];

        $builder
            ->add('title', null, ['label' => 'Titre'])
            ->add('description', null, ['label' => 'Description'])
            ->add('published', CheckboxType::class, [
                'label' => 'Publiée (visible par les autres membres)',
                'required' => false,
            ])
            ->add('subwoofers', EntityType::class, [
                'class' => Subwoofer::class,
                'label' => 'Caissons de basse exposés',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                // seulement les caissons de la collection du créateur de la vitrine
                'query_builder' => fn (SubwooferRepository $repository) => $repository->createQueryBuilder('s')
                    ->andWhere('s.soundSystem = :soundSystem')
                    ->setParameter('soundSystem', $soundSystem)
                    ->orderBy('s.brand', 'ASC'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Showcase::class,
        ]);
        $resolver->setRequired('sound_system');
        $resolver->setAllowedTypes('sound_system', SoundSystem::class);
    }
}
