<?php

namespace Deployer;

require 'recipe/symfony.php';
require 'recipe/deploy/cleanup.php';

// Config
set('repository', function () {
    return 'file://' . get('repo_name');
});

set('branch', 'master');

add('shared_files', ['.env.local']);
add('shared_dirs', ['var/log', 'config/firebase']);
add('writable_dirs', ['var', 'var/cache', 'var/log']);

// Hosts
host(localhost())
    ->set('keep_releases', 10);

// Tasks
task('deploy:dump-env', function () { 
    run('cd {{release_path}} && composer dump-env prod'); 
});

// Helper function to send email
function send_email($subject, $message)
{
    $headers = sprintf(
        'From: "Symfony Chat Deploy" <%s>' . "\r\n" . 'X-Mailer: PHP/%s',
        get('deploy_email'),
        phpversion()
    );

    mail(
        to: get('admin_email'),
        subject: $subject,
        message: $message,
        additional_headers: $headers,
        additional_params: '-f' . get('deploy_email')
    );
}

// Task to send email on successful deploy
task('notify:success', function () {
    send_email('Symfony chat Deployment Successful', 'The deployment was successful.');
});

// Task to send email on failed deploy
task('notify:failure', function () {
    send_email('Symfony chat Deployment Failed', 'The deployment failed.');
});

// Task to send email on start deploy
task('notify:start', function () {
    send_email('Symfony chat Deployment Started', 'The deployment was started.');
});

// Hooks
after('deploy:failed', 'deploy:unlock');

before('deploy:symlink', 'database:migrate');
before('deploy:cache:clear', 'deploy:dump-env');
after('deploy:failed', 'notify:failure');
after('deploy:success', 'notify:success');
before('deploy', 'notify:start');
