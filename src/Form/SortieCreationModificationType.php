<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreationModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom de la sortie :',
            ])
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
            ])
            ->add('duree', null, [
                'widget' => 'single_text',
                'label' => 'Durée (en minutes)'
            ])
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionMax', null,[
                'label' => 'Nombre de places',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label'=>'Description et infos',
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('ville', EntityType::class, [
                'label' => 'ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false,
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'attr' => [
                    'class' => 'lieu-select',
                ],
            ])
            ->add('rue', TextType::class, [
                'required' => false,
                'label' => 'Rue',
                'mapped' => false,
                'attr' => ['readonly' => true],
            ])
            ->add('latitude', TextType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['readonly' => true],
            ])
            ->add('longitude', TextType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => ['readonly' => true],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
