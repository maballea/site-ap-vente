<?php

namespace App\Form;

use App\Entity\Client; // ou Administrateur
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('mdp', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password'], // Prévenir les remplissages automatiques
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class, // ou Administrateur
        ]);
    }
}
