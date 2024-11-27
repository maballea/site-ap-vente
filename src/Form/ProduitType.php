<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('categorie', EntityType::class, [ // Ajout du champ catégorie
                'class' => Categorie::class,
                'choice_label' => 'nom', // Utilise le nom de la catégorie comme label
                'placeholder' => 'Choisissez une catégorie', // Optionnel, pour avoir une valeur par défaut
                'required' => true, // Rend la catégorie obligatoire
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
