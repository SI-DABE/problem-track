<?php

$problemsToJson = [];

foreach ($problems as $problem) {
    $problemsToJson[] = ['id' => $problem->getId(), 'title' => $problem->getTitle()];
}

$json['problems'] = $problemsToJson;
$json['pagination'] = [
    'page'                       => $paginator->getPage(),
    'per_page'                   => $paginator->perPage(),
    'total_of_pages'             => $paginator->totalOfPages(),
    'total_of_registers'         => $paginator->totalOfRegisters(),
    'total_of_registers_of_page' => $paginator->totalOfRegistersOfPage(),
];
