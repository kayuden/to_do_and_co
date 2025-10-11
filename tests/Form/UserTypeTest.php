<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'username' => 'testuser',
            'email' => 'test@exemple.com',
            'plainPassword' => [
                'first' => 'password123',
                'second' => 'password123'
            ],
            'roles' => ['ROLE_USER']
        ];

        $user = new User();
        $form = $this->factory->create(UserType::class, $user);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@exemple.com', $user->getEmail());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testFormFields(): void
    {
        $form = $this->factory->create(UserType::class);
        $formFields = $form->all();

        // test champs
        $this->assertArrayHasKey('username', $formFields);
        $this->assertArrayHasKey('plainPassword', $formFields);
        $this->assertArrayHasKey('email', $formFields);
        $this->assertArrayHasKey('roles', $formFields);

        // test types de champs
        $this->assertInstanceOf(TextType::class, $formFields['username']->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(RepeatedType::class, $formFields['plainPassword']->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(EmailType::class, $formFields['email']->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(ChoiceType::class, $formFields['roles']->getConfig()->getType()->getInnerType());
    }

    public function testFormConfiguration(): void
    {
        $form = $this->factory->create(UserType::class);

        // test labels
        $this->assertEquals("Nom d'utilisateur", $form->get('username')->getConfig()->getOption('label'));
        $this->assertEquals('Adresse email', $form->get('email')->getConfig()->getOption('label'));

        // test configuration du champ roles
        $rolesField = $form->get('roles');
        $this->assertEquals('Rôle', $rolesField->getConfig()->getOption('label'));
        $this->assertEquals([
            'Utilisateur' => 'ROLE_USER',
            'Administrateur' => 'ROLE_ADMIN'
        ], $rolesField->getConfig()->getOption('choices'));
        $this->assertTrue($rolesField->getConfig()->getOption('expanded'));
        $this->assertTrue($rolesField->getConfig()->getOption('multiple'));
    }

    public function testPasswordConfiguration(): void
    {
        $form = $this->factory->create(UserType::class);
        $passwordField = $form->get('plainPassword');

        $this->assertInstanceOf(RepeatedType::class, $passwordField->getConfig()->getType()->getInnerType());
        $this->assertEquals(PasswordType::class, $passwordField->getConfig()->getOption('type'));
        $this->assertEquals('Les deux mots de passe doivent correspondre.', $passwordField->getConfig()->getOption('invalid_message'));
        $this->assertEquals('Mot de passe', $passwordField->getConfig()->getOption('first_options')['label']);
        $this->assertEquals('Tapez le mot de passe à nouveau', $passwordField->getConfig()->getOption('second_options')['label']);
    }

    public function testOptionalPassword(): void
    {
        $form = $this->factory->create(UserType::class, null, [
            'require_password' => false
        ]);

        $this->assertFalse($form->get('plainPassword')->getConfig()->getOption('required'));
    }

    public function testDisabledRoleEditing(): void
    {
        $form = $this->factory->create(UserType::class, null, [
            'disable_role_editing' => true
        ]);

        $this->assertTrue($form->get('roles')->getConfig()->getOption('disabled'));
    }
}