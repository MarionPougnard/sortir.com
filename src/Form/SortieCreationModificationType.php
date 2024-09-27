<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreationModificationType extends AbstractType
{
    public function __construct(LieuRepository $lieuRepository)
    {
        $this->lieuRepository = $lieuRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label'=>'Nom de la sortie',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée (en minutes)'
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription"
            ])
            ->add('nbInscriptionMax', NumberType::class,[
                'label' => 'Nombre de places',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label'=>'Description et infos',
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'placeholder' => '-- Sélectionner un campus --',
                'choice_label' => 'nom',
            ])
            ->add('ville', EntityType::class, [
                'label' => 'ville',
                'class' => Ville::class,
                'placeholder' => '-- Sélectionner une ville --',
                'choice_label' => 'nom',
                'mapped' => false,
                'required' => false,
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'lieu',
                'class' => Lieu::class,
                'placeholder' => '-- Sélectionner un lieu --',
                'choice_label' => 'nom',
                'required' => true,
                'mapped' => true,
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
            'csrf_protection' => true,
            'csrf_token_id' => 'sortie_creation_modification',
        ]);
    }
}
