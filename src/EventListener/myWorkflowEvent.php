<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\EnterEvent;
use Symfony\Component\Workflow\Registry;
use \Pimcore\Model\DataObject;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DocumentEvent;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;
use function PHPUnit\Framework\throwException;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Pimcore\Db;

class myWorkflowEvent implements EventSubscriberInterface
{
    public function _construct(Registry $registry)
    {
        $this->workflowRegistry = $registry;
    }

    public function Approve(TransitionEvent $event)
    {
        //dd('hii');
        $transition=$event->getTransition();
        $workflowState=$transition->getName();
        //dd('workflowState');
        if($workflowState === 'Approve')
        {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            $products->setWorkflowState('Approve');
            $products->save();
        }

    }

    public function rejected(TransitionEvent $event)
    {
        $transition=$event->getTransition();
        $workflowState=$transition->getName();
       
        if($workflowState === 'Reject')
        {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            $products->setWorkflowState('Rejected');
            $products->save();
        }
    }

    public function Approved(TransitionEvent $event)
    {
        $transition=$event->getTransition();
        $workflowState=$transition->getName();
        if($workflowState === 'Approved')
        {
            $product = $event->getSubject();
            $id = $product->getId();
            $products = Product::getById($id);
            $products->setWorkflowState('Approved');
            $products->save();
        }
    }

    public function UpdateFolderOnCreate(ElementEventInterface $event)
    {
        if($event instanceof DataObjectEvent) 
        {
            $object = $event->getObject();
           
            if($object instanceof Product) 
            {
                $id = $object->getId();
                $productCount = 0;
                $cat=$object->getCategory();
                //dd( $cat);
                $category = Category::getById($cat->getId());
                $productCount = $category->getProductCount()+1;
                $category->setProductCount($productCount);
                $category->save();
            }
        }
    }
   
    public static function getSubscribedEvents()
    {
        return [
            'workflow.product_workflow.transition.Approve'  => 'Approve',
            'workflow.product_workflow.transition.Approved' => 'Approved',
            'workflow.product_workflow.transition.Reject'   => 'rejected',
        ];
    }
}