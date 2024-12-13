<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\produit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference')
            ->add('dateCreation')
            ->add('datecreation', null, [
                'widget' => 'single_text',
            ])
            ->add('status')
            ->add('total')
            ->add('adresseLivraison')
            ->add('datelivraisonEstimee', null, [
                'widget' => 'single_text',
            ])
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('produits', EntityType::class, [
                'class' => produit::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
