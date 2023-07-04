<?php

namespace App\test\Unit\Entity;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class userRegistrationTest extends WebTestCase
{

    public function testLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userrepo = $container->get(UserRepository::class);
        $testUser = $userrepo->find(1);
        $client->loginUser($testUser);
        $client->request('GET', '/TeamManager/Dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Project Status');
    }
    public function testRegister(): void
    {
        $client = static::createClient();
        $crawler = $client->request("POST", "/register");
        $form = $crawler->selectButton('registerBtn')->form();

        $formData = [
            "registration_form[Firstname]" => "kartik",
            "registration_form[LastName]" => "kartik",
            "registration_form[ContactNumber]" => 9099998787,
            "registration_form[email]" => "tma1a".random_int(162,22222)."@szssgaaamail.com",
            "registration_form[plainPassword]" => "Aa@123456",
            "registration_form[agreeTerms]" => true
        ];
        foreach ($formData as $key => $val) {
            $form[$key]->setValue($val);
        }
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Verify OTP');
    }
}
