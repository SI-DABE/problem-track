<?php

foreach ($problems as $problem) {
    $json[] = ['id' => $problem->getId(), 'title' => $problem->getTitle()];
}
