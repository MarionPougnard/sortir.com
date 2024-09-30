<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus',
                'placeholder' => 'Choisissez un campus',
            ])
            ->add('pseudo', TextType::class, ['label' => 'Pseudo'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('telephone', TextType::class, ['label' => 'Téléphone', 'attr' => ['placeholder' => '+33_  _ _  _ _  _ _  _ _']])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('photo', FileType::class, [
                'mapped' => false,
                'data_class' => null,
                'label' => 'Photo',
                'attr' => ['accept' => 'images/*'],
                'required' => false])
            ->add('motDePasse', PasswordType::class, [
                'mapped' => false,  // Si vous ne souhaitez pas mapper ce champ à l'entité Utilisateur
                'required' => false, // Peut être facultatif selon votre logique
                'label' => 'Mot de Passe'
            ])
            ->add('confirmMotDePasse', PasswordType::class, [
                'mapped' => false,  // Ne mappe pas ce champ à l'entité
                'required' => false,
                'label' => 'Confirmation'
            ])
            ->add('estActif', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false
            ]);
//            ->add('save', SubmitType::class, [
//                'label' => 'Enregistrer',
//                'attr' => ['class' => 'btn btn-success']
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
