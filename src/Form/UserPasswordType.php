<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UserPasswordType extends AbstractType
{
  /**
   * @var string
   */
  private const MAPPED = 'mapped';
  /**
   * @var string
   */
  private const LABEL = 'label';
  /**
   * @var string
   */
  private const CONSTRAINTS = 'constraints';
  /**
   * @var string
   */
  private const MESSAGE = 'message';
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder

      ->add('oldPassword', PasswordType::class, [
        self::MAPPED => false,
        self::LABEL => ' Старый пароль',
        self::CONSTRAINTS => [
          new UserPassword([
            self::MESSAGE => 'Неверный пароль',
          ]),
        ],
      ])
      ->add('plainPassword', PasswordType::class, [
        self::LABEL => 'Новый пароль',
        // instead of being set onto the object directly,
        // this is read and encoded in the controller
        self::MAPPED => false,
        self::CONSTRAINTS => [
          new NotBlank([
            self::MESSAGE => 'Пожалуйста, введите пароль',
          ]),
          new Length([
            'min' => 6,
            'minMessage' =>
              'Пароль должен содержать минимум {{ limit }} символов',
            // max length allowed by Symfony for security reasons
            'max' => 4096,
          ]),
        ],
      ])
      ->add('repeatPassword', PasswordType::class, [
        self::LABEL => 'Повторите новый пароль',
        self::MAPPED => false,
        self::CONSTRAINTS => [
          new NotBlank([
            self::MESSAGE => 'Пожалуйста, повторите пароль',
          ]),
        ],
      ])
      ->add('submit', SubmitType::class, [
        self::LABEL => 'Сохранить',
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => User::class,
    ]);
  }
}
