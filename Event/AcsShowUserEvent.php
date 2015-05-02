<?php

namespace ACS\ACSPanelUsersBundle\Event;

use Avanzu\AdminThemeBundle\Event\ShowUserEvent;
use ACS\ACSPanelUsersBundle\Entity\FosUser;

class AcsShowUserEvent
{
    public function onShowUser(ShowUserEvent $event)
    {
        $user = $this->getUser();
        $event->setUser($user);
    }

    protected function getUser()
    {
    }
}
