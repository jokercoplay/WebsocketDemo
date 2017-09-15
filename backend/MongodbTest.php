<?php 
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    $bulk = new MongoDB\Driver\BulkWrite;
    $document = ['name' => 'hai'];
    
    $bulk->insert($document);

    $bulk->update(
        ['name' => 'dnoy'],
        ['$set' => ['name' => 'dony.dang']]
    );

    $bulk->delete(['name' => 'hai']);

    $manager->executeBulkWrite('test.user', $bulk);


    $filter = ['name' => 'hai'];
    $query = new MongoDB\Driver\Query($filter);
    $res = $manager->executeQuery('test.user', $query);
