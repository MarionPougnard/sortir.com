<?php

namespace App\Form;

use App\DTO\RechercheSortie;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'placeholder' => '--Sélectionner un campus--',
                'required' => false,
            ])
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient',
            ])
            ->add('dateDebut', DateType::class,
                [   'widget' => 'single_text',
                    'required' => false,
                    'label' => 'Entre',
                ])
            ->add('dateFin', DateType::class,
                [   'widget' => 'single_text',
                    'required' => false,
                    'label' => 'et',
                ])
            ->add('estOrganisateur', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur.trice",
            ])
            ->add('estInscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je suis inscrit.e",
            ])
            ->add('estPasInscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je ne suis pas inscrit.e",
            ])
            ->add('estTerminees', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties terminées",
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RechercheSortie::class,
        ]);
    }

}
