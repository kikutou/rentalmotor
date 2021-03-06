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
 * of the License, or (at your option) any later version.
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


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchQuestionnaireType extends AbstractType
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->config;
        $builder
            // アンケートID・会員メールアドレス・会員名前・会員名前(フリガナ)
            ->add('multi', 'text', array(
                'label' => 'アンケートID・会員メールアドレス・会員名前・会員名前(フリガナ)',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('question1', 'question1', array(
                'label' => '問題1',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question2', 'question2', array(
                'label' => '問題2',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question3_start', 'date', array(
                'label' => '問題3',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('question3_end', 'date', array(
                'label' => '問題3',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('question4', 'question4', array(
                'label' => '問題1',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question5', 'question5', array(
                'label' => '問題5',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question6', 'question6', array(
                'label' => '問題6',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question7', 'question7', array(
                'label' => '問題7',
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('question8', 'text', array(
                'label' => '問題8',
                'required' => false
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_questionnaire';
    }
}
