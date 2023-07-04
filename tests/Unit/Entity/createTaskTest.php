<?php

namespace App\test\Unit\Entity;

use App\Entity\TaskWithProject;
use App\Repository\TaskWithProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class createTaskTest extends WebTestCase
{
    public function testCreateTask(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $testUser = $userrepo->find(5);
        $client->loginUser($testUser);
        $client->request('GET', '/ProjectManager/Dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Projects');
        $crawler = $client->request("POST", "/ProjectManager/Dashboard/Projects/Tasks/Create?id=1");
        // $crawler = $client->request("POST", "/ProjectManager/Dashboard/Projects/Tasks/Create", ['id' => 2]);
        $form = $crawler->selectButton('createTaskBtn')->form();
        $formData = [
            "create_task[Title]" => "kartik",
            "create_task[Description]" => "kartik",
            "create_task[Priority]" => TaskWithProject::TASK_PRIORITY_HIGH,
            "create_task[Status]" => TaskWithProject::TASK_STATUS_OPEN,
            "create_task[ActualStartDate]" => "2023-05-21",
            "create_task[ActualEndDate]" => "2023-05-22",
            // "create_task[ActualStartDate][month]" => "1",
            // "create_task[ActualStartDate][day]" => "1",
            // "create_task[ActualEndDate][day]" => "2",
            // "create_task[ActualEndDate][month]" => "1",
            // "create_task[ActualEndDate][year]" => "2023",
        ];
        foreach ($formData as $key => $val) {
            $form[$key]->setValue($val);
        }
        $form["assignedTo"][0]->tick();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('New task Have been assigned to ', $client->getResponse()->getContent());
    }
    public function testUpdateTask(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $testUser = $userrepo->find(5);
        $client->loginUser($testUser);
        $taskWithProjectRepository = $container->get(TaskWithProjectRepository::class);
        $taskArray = $taskWithProjectRepository->findAllTaskForProjectQueryBuilder(1)->getQuery()->getResult();
        if ($taskArray) {
            $crawler = $client->request("POST", "/Tasks/View?id=1&tid=2", ['id' => 1, 'tid' => $taskArray[0]->getId()]);
            $form = $crawler->selectButton('createTaskBtn')->form();
            $formData = [
                "create_task[Title]" => "cccc",
                "create_task[Description]" => "tttttttttt",
                "create_task[Priority]" => TaskWithProject::TASK_PRIORITY_HIGH,
                "create_task[Status]" => TaskWithProject::TASK_STATUS_OPEN,
                "create_task[ActualStartDate]" => "2023-05-21",
                "create_task[ActualEndDate]" => "2023-05-22",
            ];
            foreach ($formData as $key => $val) {
                $form[$key]->setValue($val);
            }
            $form["assignedTo"][0]->tick();
            $client->submit($form);
            $crawler = $client->followRedirect();
            $crawler = $client->followRedirect();
            $this->assertStringContainsString('Task updated Successfully', $client->getResponse()->getContent());
        }
        $client->request('GET', '/ProjectManager/Dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Projects');
    }
}

    // dd($client->getResponse()->getContent());
