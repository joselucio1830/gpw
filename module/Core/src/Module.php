<?php

namespace Core;

use Core\Api\Connect;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\TransportInterface;
use Zend\ServiceManager\ServiceManager;

class Module
{
    const VERSION = '3.0.2dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                TransportInterface::class => function (ServiceManager $serviceManager) {


                    $config = $serviceManager->get('Config');
                    if (!empty($config['mail'])) {
                        $id = array_rand($config['mail']['transports']['options']);
                        $transport = new Smtp();
                        $transport->setOptions(new SmtpOptions($config['mail']['transports']['options'][$id]));


                        return $transport;
                    }
                    return false;
                },
                'log' => function ($sm) {
                    $config = $sm->get('config');

                    $dst = 'php://output';
                    if (!empty($config['log']['logFile']) && file_exists($config['log']['logFile']) && is_writeable($config['log']['logFile'])) {
                        $dst = $config['log']['logFile'];
                    }

                    $logger = new Logger;
                    $writer = new Stream($dst);
                    
                    $logger->addWriter($writer);

                    return $logger;

                },

            ),
        );
    }
}
