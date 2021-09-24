<?php

namespace Deployer;

// Include the Laravel & rsync recipes
require 'recipe/laravel.php';
require 'recipe/rsync.php';

set('application', 'traleado-app');
set('keep_releases', 2);
set('ssh_multiplexing', true); // Speed up deployment

set('rsync_src', function () {
    return __DIR__; // If your project isn't in the root, you'll need to change this.
});

// Configuring the rsync exclusions.
// You'll want to exclude anything that you don't want on the production server.
add('rsync', [
    'exclude' => [
        '.git',
        '/.env',
        '/vendor/',
        '/node_modules/',
        '.github',
        'deploy.php',
    ],
]);

// Set up a deployer task to copy secrets to the server.
// Grabs the dotenv file from the github secret
task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

// Hosts
host('testing.traleado.com') // Name of the server
    ->hostname('54.253.170.33') // Hostname or IP address
    ->stage('testing') // Deployment stage (production, staging, etc)
    ->user('root') // SSH user
    ->set('deploy_path', '/var/www/traleado'); // Deploy path

// Hosts
host('staging.traleado.com') // Name of the server
    ->hostname('13.238.195.194') // Hostname or IP address
    ->stage('staging') // Deployment stage (production, staging, etc)
    ->user('root') // SSH user
    ->set('deploy_path', '/var/www/traleado'); // Deploy path

// Hosts
host('traleado.com') // Name of the server
    ->hostname('54.206.47.189') // Hostname or IP address
    ->stage('production') // Deployment stage (production, staging, etc)
    ->user('root') // SSH user
    ->set('http_user', 'ubuntu')
    ->set('deploy_path', '/var/www/traleado'); // Deploy path

after('deploy:failed', 'deploy:unlock'); // Unlock after failed deploy

desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync', // Deploy code & built assets
    'deploy:secrets', // Deploy secrets
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',         // |
    'artisan:view:cache',           // |
    'artisan:config:cache',         // | Laravel specific steps
    'artisan:optimize',             // |
    'artisan:migrate',              // |
    'artisan:lada-cache:flush',     // |
    'deploy:symlink',
    'deploy:unlock',
    'perm:assign',
    'perm:storage',
    'cleanup',
]);

task('reload:php-fpm', function () {
    run('sudo /usr/sbin/service php7-fpm reload');
});

task('perm:assign', function () {
    run('cd {{release_path}} && sudo chown -R ubuntu:www-data *');
});

task('artisan:lada-cache:flush', function () {
    run('cd {{release_path}} && php artisan lada-cache:flush');
});

task('perm:storage', function () {
    run('sudo chmod -R 775 /var/www/traleado/shared/storage');
    run('sudo chown -R ubuntu:www-data /var/www/traleado/shared/storage');
});

task('reload:nginx', function () {
    run('sudo nginx -s reload');
});
