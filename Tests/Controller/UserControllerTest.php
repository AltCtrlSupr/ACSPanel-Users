<?php
namespace ACS\ACSPanelUsersBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use ACS\ACSPanelBundle\Tests\Controller\CommonTestCase;

class UserControllerTest extends CommonTestCase
{
    public function testIndex()
    {
        $this->client = $this->createAuthorizedClient('superadmin','1234');
	$client = $this->client;

        $crawler = $client->request('GET', '/users');

        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/users/show/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

    }

    public function testSwitchUser()
    {
        $this->client = $this->createAuthorizedClient('superadmin','1234');
	$client = $this->client;

        $crawler = $client->request('GET', '/users/switch/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }

    public function testUserEdit()
    {
        $this->client = $this->createAuthorizedClient('superadmin','1234');
	$client = $this->client;

        $crawler = $client->request('GET', '/users/switch/1');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }

}
