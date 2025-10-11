<?php

namespace App\Tests\Form;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TaskTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Titre tâche',
            'content' => 'Contenu de la tâche',
        ];

        $task = new Task();
        $form = $this->factory->create(TaskType::class, $task);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('Titre tâche', $task->getTitle());
        $this->assertEquals('Contenu de la tâche', $task->getContent());
    }

    public function testFormFields(): void
    {
        $form = $this->factory->create(TaskType::class);
        $formFields = $form->all();

        // test champs
        $this->assertArrayHasKey('title', $formFields);
        $this->assertArrayHasKey('content', $formFields);

        // test types de champs
        $this->assertInstanceOf(TextType::class, $formFields['title']->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(TextareaType::class, $formFields['content']->getConfig()->getType()->getInnerType());
    }

    public function testFormConfiguration(): void
    {
        $form = $this->factory->create(TaskType::class);

        // test labels
        $this->assertEquals('Titre', $form->get('title')->getConfig()->getOption('label'));
        $this->assertEquals('Contenu', $form->get('content')->getConfig()->getOption('label'));

        // test l'attribut rows pour le textarea
        $this->assertEquals(
            ['rows' => 6],
            $form->get('content')->getConfig()->getOption('attr')
        );
    }
}