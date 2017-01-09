<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * CLI faucet starting point
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
$cradle->on('faucet', include __DIR__ . '/cli/faucet.php');

/**
 * CLI help menu
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
$cradle->on('faucet-help', include __DIR__ . '/cli/help.php');

/**
 * CLI queue - bin/cradle faucet queue auth-verify auth_slug=<email>
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
$cradle->on('faucet-queue', include __DIR__ . '/cli/queue.php');

/**
 * CLI starts worker
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-work', include __DIR__ . '/cli/work.php');

/**
 * CLI Deploy
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-deploy-production', include __DIR__ . '/cli/deploy/production.php');

/**
 * CLI Deploy
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-deploy-s3', include __DIR__ . '/cli/deploy/s3.php');

/**
 * CLI production connect
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-connect-to', include __DIR__ . '/cli/deploy/connect.php');

/**
 * CLI clear cache
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-flush-redis', include __DIR__ . '/cli/redis/flush.php');

/**
 * CLI clear index
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-flush-elastic', include __DIR__ . '/cli/elastic/flush.php');

/**
 * CLI map index
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-map-elastic', include __DIR__ . '/cli/elastic/map.php');

/**
 * CLI clear index
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-populate-elastic', include __DIR__ . '/cli/elastic/populate.php');

/**
 * CLI clear index
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-flush-sql', include __DIR__ . '/cli/sql/flush.php');

/**
 * CLI populates database with dummy data
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-build-sql', include __DIR__ . '/cli/sql/build.php');

/**
 * CLI populates database with dummy data
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-populate-sql', include __DIR__ . '/cli/sql/populate.php');

/**
 * CLI faucet installation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-install', include __DIR__ . '/cli/install.php');

/**
 * CLI faucet update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-update', include __DIR__ . '/cli/update.php');

/**
 * CLI faucet server
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-server', include __DIR__ . '/cli/server.php');

/**
 * CLI app generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-app', include __DIR__ . '/cli/generate/app.php');

/**
 * CLI module generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-module', include __DIR__ . '/cli/generate/module.php');

/**
 * CLI admin generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-admin', include __DIR__ . '/cli/generate/admin.php');

/**
 * CLI REST generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-rest', include __DIR__ . '/cli/generate/rest.php');

/**
 * CLI SQL generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-sql', include __DIR__ . '/cli/generate/sql.php');

/**
 * CLI Elastic generate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('faucet-generate-elastic', include __DIR__ . '/cli/generate/elastic.php');
