<?php

use Sami\Sami;
use Sami\RemoteRepository\GitHubRemoteRepository;

return new Sami('src', array(
    'title'                => 'AnunaFramework API',
    'build_dir'            => __DIR__ .'/api',
    'cache_dir'            => __DIR__ .'/cache',
    'remote_repository'    => new GitHubRemoteRepository('Anunatak/framework-docs', 'api/'),
    'default_opened_level' => 2,
));