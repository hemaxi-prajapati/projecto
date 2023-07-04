<?php

namespace App\test\Unit\Entity;

use App\Entity\TaskWithProject;
use App\Repository\ProjectDetailsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class createProjectTest extends WebTestCase
{
    public function testCreateProject(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $testUser = $userrepo->find(5);
        $client->loginUser($testUser);
        $client->request('GET', '/ProjectManager/Dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Projects');
        $crawler = $client->request("POST", "/ProjectManager/Dashboard/Projects/Create");
        $form = $crawler->selectButton('createProjectBtn')->form();
        $formData = [
            "create_project[Name]" => "kartik",
            "create_project[Description]" => "kartik",
            "create_project[Status]" => "Open",
            "create_project[StartDate]" => "2023-05-21",
            "create_project[EndDate]" => "2023-05-22",
        ];
        foreach ($formData as $key => $val) {
            $form[$key]->setValue($val);
        }
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Project Created', $client->getResponse()->getContent());
    }
    public function testUpdateProject(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $testUser = $userrepo->find(5);
        $client->loginUser($testUser);
        $projectDetailsRepository = $container->get(ProjectDetailsRepository::class);
        $showProjects = ($projectDetailsRepository->findAllProjectsQueryBuilder($testUser))->getQuery()->getResult();

        if ($showProjects) {
            // $crawler = $client->request('GET', '/ProjectManager/Dashboard/Projects/manage', ["id" => 1]);
            $crawler = $client->request('GET', '/ProjectManager/Dashboard/Projects/manage?id=' . $showProjects[0]->getId());
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', 'Manage Project');

            // dd($client->getResponse()->getContent());
            $form = $crawler->selectButton('createProjectBtn')->form();
            $formData = [
                "create_project[Name]" => "xxxx",
                "create_project[Description]" => "kartik",
                "create_project[Status]" => "Open",
                "create_project[StartDate]" => "2023-05-21",
                "create_project[EndDate]" => "2023-05-22",
            ];
            foreach ($formData as $key => $val) {
                $form[$key]->setValue($val);
            }
            $client->submit($form);
            $crawler = $client->followRedirect();
            // dd($testUser);
            // dd($client->getResponse()->getContent());
            $this->assertStringContainsString('Project Details Updated', $client->getResponse()->getContent());
        } else {
            $client->request('GET', '/ProjectManager/Dashboard');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', 'Projects');
        }
        // }

    }
}

    // dd($client->getResponse()->getContent());
