<?php
/**
 * Created by PhpStorm.
 * User: evis
 * Date: 4/24/17
 * Time: 2:30 PM
 */

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Uecode\Bundle\QPushBundle\Event\MessageEvent;

class ProcessMessage
{
    use ContainerAwareTrait;

    public function __construct($container)
    {
        $this->setContainer($container);
    }

    public function onMessageReceived(MessageEvent $event)
    {
        $queue_name = $event->getQueueName();
        $message    = $event->getMessage();
        $body       = $event->getMessage()->getBody();

        $this->container->get("logger")->notice($queue_name);
        $this->container->get("logger")->notice(print_r($body, true));
    }
}