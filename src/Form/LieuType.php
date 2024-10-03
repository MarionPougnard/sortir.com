<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom du lieu']
            ])

            ->add('rue', TextType::class, [
                'label' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom de la rue',]
            ])

            ->add('latitude', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 6,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Latitude'],
            ])

            ->add('longitude', NumberType::class, [
                'label' => false,
                'required' => false,
                'scale' => 6,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Longitude'],
            ])

            ->add('ville', EntityType::class, [
                'label' => false,
                'class' => Ville::class,
                'placeholder' => 'Sélectionner une ville',
                'choice_label' => 'nom',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
