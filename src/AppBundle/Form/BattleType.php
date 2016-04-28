<?php

namespace AppBundle\Form;

use AppBundle\Entity\Programmer;
use AppBundle\Entity\Project;
use AppBundle\Form\Model\BattleModel;
use AppBundle\Repository\ProgrammerRepository;
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
        $user = $options['user'];

        $builder
            ->add('projectId', EntityType::class, ['class' => Project::class, 'property_path' => 'project'])
            ->add(
                'programmerId',
                EntityType::class,
                [
                    'class'         => Programmer::class,
                    'property_path' => 'programmer',
                    'query_builder' => function (ProgrammerRepository $programmerRepository) use ($user) {
                        $programmerRepository->createQueryBuilderForUser($user);
                    }
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => BattleModel::class,
                'csrf_protection' => false,
            ]
        );

        $resolver->setRequired('user');
    }
}