<?php

namespace SmartCore\Module\Menu\Entity;

use Doctrine\ORM\EntityRepository;

class ItemRepository extends EntityRepository
{
    public function findByParent(Item $parent_item = null)
    {
        return $this->findBy(['parent_item' => $parent_item]);
    }
}
