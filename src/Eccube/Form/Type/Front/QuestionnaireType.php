<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) an
 * y later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionnaireType extends AbstractType
{
    public $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question1', 'question1', array(
                'required' => false,
            ))
            ->add('question2', 'question2', array(
                'required' => false,
            ))
            ->add('question3', 'date', array(
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'Wrong date format',
                'attr' => array(
                    'placeholder' => '例：1990-01-01'
                ),
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('question4', 'question4', array(
                'required' => false,
            ))
            ->add('question5', 'question5', array(
                'required' => false,
            ))
            ->add('question6', 'question6', array(
                'required' => false,
            ))
            ->add('question7', 'question7', array(
                'required' => false,
            ))
            ->add('question8', 'textarea', array(
                'required' => false,
            ))

            ->add('question1_note', 'text', array(
                'required' => false
            ))
            ->add('question4_note', 'text', array(
                'required' => false
            ))
            ->add('question5_note', 'text', array(
                'required' => false
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Questionnaire',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'questionnaire';
    }
}
