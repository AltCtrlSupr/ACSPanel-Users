<?php

namespace ACS\ACSPanelUsersBundle\Tests\Controller;

use ACS\ACSPanelUsersBundle\Controller\UserController as Con;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testIndex()
    {

	    $con = new Con();
	    ldd($con);
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');

    }

}
