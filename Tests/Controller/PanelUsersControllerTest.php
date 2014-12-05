<?php

namespace ACS\ACSPanelUsersBundle\Tests\Controller;

use ACS\ACSPanelBundle\Tests\Controller\CommonTestCase;

class PanelUserControllerTest extends CommonTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $this->client = $this->createAuthorizedClient('superadmin','1234');

        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/users/');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }
}
