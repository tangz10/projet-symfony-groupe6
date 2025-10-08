<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Tous les sites',
            ])
            ->add('q', SearchType::class, [
                'required' => false,
                'label' => 'Nom contient',
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre',
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'et',
            ])
            ->add('isOrganisateur', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties dont je suis l'organisateur/trice",
            ])
            ->add('isInscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je suis inscrit/e",
            ])
            ->add('isNotInscrit', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties auxquelles je ne suis pas inscrit/e",
            ])
            ->add('isPast', CheckboxType::class, [
                'required' => false,
                'label' => "Sorties passées",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
