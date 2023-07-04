<?php

namespace App\test\Unit\Entity;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class requestForEmployeeTest extends WebTestCase
{
    public function testApprovedReject(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $projectAssignmentRepository = $container->get(ProjectAssignmentRepository::class);
        $testUser = $userrepo->find(1);
        $client->loginUser($testUser);
        $client->request('GET', '/TeamManager/Dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Project Status');
        $NeedAprovelEmployeeQueryBuilder = $projectAssignmentRepository->NeedAprovelEmployeeQueryBuilder($testUser->getDepartment()->getId())->getQuery()->getResult();
        if ($NeedAprovelEmployeeQueryBuilder) {
            $crawler = $client->request("GET", "/TeamManager/needApprovedChangeStatus", ['opration' => 'Approved', 'id' => $NeedAprovelEmployeeQueryBuilder[0]->getId()]);
            $crawler = $client->followRedirect();
            $this->assertStringContainsString('User Status :', $client->getResponse()->getContent());
        } else {
            
            $crawler = $client->request("GET", "/TeamManager/employeeRequest");
            $this->assertStringContainsString('Oops! No employees found', $client->getResponse()->getContent());
        }
        // dd($client->getResponse()->getContent());

        // $crawler = $client->request("POST", "/ProjectManager/Dashboard/Projects/Tasks/Create",['id'=>1]);

    }
}

    // dd($client->getResponse()->getContent());
