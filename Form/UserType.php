<?php

namespace WH\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{

    private $roles;

    public function __construct($options = array())
    {
        # store roles
        $this->roles    = $options['roles'];
        $this->roles    = $this->refactorRoles($this->roles);

    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('roles', 'choice', array(
                    'label' => 'Rôle(s) : ',
                    'attr' => array('class' => 'form-control select2'),
                    'required' => true,
                    'multiple' => true,
                    'choices' => $this->roles
                ))
            ->add('firstname', 'text', array('label' => 'prénom : '))
            ->add('lastname', 'text', array('label' => 'nom : '))
            ->add('civility', 'choice', array(
                'label' => 'Civivilité : ',
                'choices'   => array(
                    'Mme.' => 'Madame',
                    'M.' => 'Monsieur'
                )
            ))
            ->add('email', 'email', array('label' => 'email : '))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'APP\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'wh_user_create';
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'roles' => null
        );
    }

    /**
     * refactorRoles ne récupère que le niveau 0
     * @param $originRoles
     * @return array
     */
    private function refactorRoles($originRoles)
    {
        $roles = array();

        // Add herited roles
        foreach ($originRoles as $roleParent => $rolesHerit) {

            $roles[$roleParent] = $roleParent;

        }

        return $roles;

    }
}
