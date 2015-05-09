<?php

namespace ACS\ACSPanelUsersBundle\Tests\Model;

use ACS\ACSPanelUsersBundle\Entity\FosUser;

class FosUserTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHomeDir()
    {
        $test_user = new FosUser();
        $test_user->setUsername('administrator');

        $home_dir = $test_user->getHomeDir();

        $this->assertEquals($home_dir, $test_user->getUsername());
    }
}

