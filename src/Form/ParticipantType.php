<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // ->add('roles')
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('photoProfilFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer la photo',
                'download_uri' => false,
                'image_uri' => true,
                'asset_helper' => true,
                'label' => 'Photo de profil'
            ])
            ->add('administrateur')
            ->add('actif')
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
            ])
            /*
            ->add('ParticipantsInscrits', EntityType::class, [
                'class' => Sortie::class,
                'choice_label' => 'id',
                'multiple' => true,
            ]) */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
