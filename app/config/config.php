<?php

if (getenv('ENVIRONMENT') == 'develop') {
    $environment = 'develop';
} elseif (getenv('ENVIRONMENT') == 'staging') {
    $environment = 'staging';
} elseif (getenv('ENVIRONMENT') == 'production') {
    $environment = 'production';
} elseif (getenv('ENVIRONMENT') == 'docker') {
    $environment = 'docker';
} else {
    $environment = 'local';
}

require $environment.".config.php";

return new \Phalcon\Config($settings);
