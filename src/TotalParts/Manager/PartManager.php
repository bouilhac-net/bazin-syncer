<?php

namespace Bazin\Syncer\TotalParts\Manager;

class PartManager extends \SolutionsCVHU\Syncer\TotalParts\Manager\PartManager
{
    protected function getAvailableCriteriaString(): string
    {
        return 'opisto_part.blocked = 0 AND
                opisto_part.available = 1 AND
                opisto_part.for_sale = 1 AND
                opisto_part.in_stock = 1';
    }
}
