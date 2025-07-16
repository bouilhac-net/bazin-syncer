<?php

namespace Bazin\Syncer\FranceCasse\Manager;

class PartManager extends \SolutionsCVHU\Syncer\FranceCasse\Manager\PartManager
{
    protected function getAvailableCriteriaString(): string
    {
        return 'opisto_part.blocked = 0 AND
                opisto_part.available = 1 AND
                opisto_part.for_sale = 1 AND
                opisto_part.in_stock = 1 AND
                (s.id = 1643 OR s.id = 1722)';
    }
}
