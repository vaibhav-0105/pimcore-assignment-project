<?php

namespace App\Workflow;

use App\Model\Product\Product;

use Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class SupportsStrategy implements WorkflowSupportStrategyInterface
{
    public function supports (WorkflowInterface $workflow, $subject): bool
    {
        if($workflow->getName()=='product_workflow')
        {
            if($subject instanceof Product && strpos($subject->getFullPath(), '/upload/new') == 0)
            {
                return true;
            }
        }
        return false;
    }
}




?>