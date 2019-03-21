<?php

namespace App\Repository\Catalog\Mark;

use App\Repository\AbstractRepository;

abstract class AbstractEntityRepository extends AbstractRepository
{
    public function findOneByOid($oid)
    {
        return $this->findOneBy(['oid' => $oid]);
    }
}
