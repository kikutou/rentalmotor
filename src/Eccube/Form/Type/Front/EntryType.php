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


namespace Eccube\Form\Type\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EntryType extends AbstractType
{
    protected $app;
    protected $config;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Categories = $this->app['eccube.repository.category']->findBy(array(
            'level' => 1
        ));

        $choices = array('' => '');
        foreach ($Categories as $category) {
            $choices[$category['id']] = $category['name'];
        }

        $builder
            ->add('name', 'name', array(
                'required' => true,
            ))
            ->add('kana', 'kana', array(
                'required' => true,
            ))
            ->add('zip', 'zip')
            ->add('address', 'address')
            ->add('tel', 'tel', array(
                'required' => true,
            ))
            ->add('email', 'repeated_email')
            ->add('password', 'repeated_password')

            ->add('category_1_1', 'choice', array(
                'choices' => $choices
            ))
            ->add('category_1_2', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_1_3', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_1_4', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))

            ->add('category_2_1', 'choice', array(
                'choices' => $choices
            ))
            ->add('category_2_2', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_2_3', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_2_4', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))

            ->add('category_3_1', 'choice', array(
                'choices' => $choices
            ))
            ->add('category_3_2', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_3_3', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))
            ->add('category_3_4', 'choice', array(
                'disabled' => true,
                'choices' => array('' => '')
            ))

            ->add('save', 'submit', array('label' => 'この内容で登録する'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Customer',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        // todo entry,mypageで共有されているので名前を変更する
        return 'entry';
    }
}
