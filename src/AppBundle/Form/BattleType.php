<?php

namespace AppBundle\Form;

use AppBundle\Entity\Programmer;
use AppBundle\Entity\Project;
use AppBundle\Form\Model\BattleModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BattleType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('project', EntityType::class, ['class' => Project::class])
            ->add('programmer', EntityType::class, ['class' => Programmer::class]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BattleModel::class,
                'csrf_protection' => false
            ]
        );
    }
}