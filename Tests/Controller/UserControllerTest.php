<?php
namespace ACS\ACSPanelUsersBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use ACS\ACSPanelBundle\Tests\Controller\CommonTestCase;

class UserControllerTest extends CommonTestCase
{
    public function testIndex()
    {
        $client = $this->createSuperadminClient();

        $crawler = $client->request('GET', '/users');

        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/users/show/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

    }

    public function testSwitchUser()
    {
        $client = $this->createSuperadminClient();

        $crawler = $client->request('GET', '/users/switch/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }

	public function testNewUser()
	{
        $client = $this->createSuperadminClient();

        $crawler = $client->request('GET', '/users/new');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
	}

    public function testUserEdit()
    {
        $client = $this->createSuperadminClient();

        $crawler = $client->request('GET', '/users/edit/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }
}
