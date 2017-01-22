<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\CommandLine\Index as CommandLine;
use Cradle\Framework\Queue\Service\RabbitMQService;

/**
 * CLI queue - bin/cradle faucet queue auth-verify auth_slug=<email>
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
return function ($request, $response) {
    $data = $request->getStage();
    if (!isset($data[0])) {
        CommandLine::error('Not enough arguments. Usage: bin/cradle faucet queue event data');
    }

    $resource = cradle('global')->service('rabbitmq-main');

    if(!$resource) {
        CommandLine::error('Unable to queue, check config/services.php for correct connection information.');
    }

    $event = array_shift($data);

    $priority = 0;
    if (isset($data['priority'])) {
        $priority = $data['priority'];
        unset($data['priority']);
    }

    $delay = 0;
    if (isset($data['delay'])) {
        $delay = $data['delay'];
        unset($data['delay']);
    }

    $retry = 0;
    if (isset($data['retry'])) {
        $delay = $data['retry'];
        unset($data['retry']);
    }

    $resource = cradle('global')->service('rabbitmq-main');
    $settings = cradle('global')->config('settings');

    $queue = 'queue';
    if(isset($settings['queue'])) {
        $queue = $settings['queue'];
    }

    (new RabbitMQService($resource))
        ->setQueue($queue)
        ->setData($data)
        ->setDelay($delay)
        ->setPriority($priority)
        ->setRetry($retry)
        ->send($event);

    CommandLine::info('Queued: `' . $event . '` into `' . $queue . '`');
};
