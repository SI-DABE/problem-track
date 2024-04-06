<?php

require '/var/www/app/models/Problem.php';

$title = 'Novo Problema';
$view = '/var/www/app/views/problems/new.phtml';
$problem = new Problem();

require '/var/www/app/views/layouts/application.phtml';
