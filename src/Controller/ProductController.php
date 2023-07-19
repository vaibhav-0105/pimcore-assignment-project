<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\DemoProduct;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Db;

class ProductController extends FrontendController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        
        try{
                $listing =  new DataObject\Product\Listing();
                $listing->setUnpublished(false);
                $listing        = $listing->load();
                $workflowState  =[];
                $status         =[];
            
                foreach($listing as $workflowState)
                {
                    $proddata[] = $workflowState->getId();
                    $db         = Db::get();
                    $query      ="Select * from element_workflow_state where cid=".$workflowState->getId();// check with object
                    $stmt       = $db->prepare($query);
                    $stmt->execute();

                    if ($stmt instanceof Result) { 
                        $checkSatus =$stmt->fetchAssociative(); 
                        $status[$workflowState->getId()]=$checkSatus['place'];
                    } 

                // $checkSatus = $stmt->fetch();
                }
                return $this->render('product/listdata.html.twig',['entries' => $listing]);
            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }

    public function addAction(Request $request): Response
    {
        return $this->render('product/add.html.twig');
    }

    public function createAction(Request $request): Response
    {
        try {
                $name  = $request->get('name');
                $color = $request->get('color');
                $workflowState = $request->get('workflowState');
                
                $uploadedImage      = $request->files->get('image');
                $distinationPath    = $this->getParameter('kernel.project_dir'). '/public/var/assets/';

                $originalFileName   = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName        = $originalFileName.'-'.uniqid().'.'.$uploadedImage->guessExtension();
                
                $uploadedImage->move($distinationPath,$newFileName);
                $originalFullFileNamePath = $distinationPath.$newFileName;
                
                $asset =  new \Pimcore\Model\Asset();
                $asset->setParentId(1);
                $asset->setFilename($newFileName);
                $asset->setData(file_get_contents($originalFullFileNamePath));

                $asset->save();

                $latestAssetsId = $asset->getId();
                $image          = Asset\Image::getById($latestAssetsId);	
                
                $newObject = new DataObject\Product();
                $newObject->setKey(trim($name));
                $newObject->setParent(DataObject\Folder::getByPath("/Product"));

                $newObject->setName($name);
                $newObject->setColor($color);
                $newObject->setImage($image);
                $newObject->setPublished(true);
                $newObject->setWorkflowState('Created');
            
                $newObject->save();
            
                return $this->redirect('/listAction');
            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }

    public function updateAction(Request $request): Response
    {
        try{
                $name           = $request->get('name');
                $color          = $request->get('color');  
                $ObjectId       = $request->get('ObjectId');    
                $uploadedImage  = $request->files->get('image');
                $workflowState  = $request->get('workflowState');
                
                $distinationPath    = $this->getParameter('kernel.project_dir'). '/public/var/assets/';
                $originalFileName   = pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_FILENAME);
                $newFileName        = $originalFileName.'-'.uniqid().'.'.$uploadedImage->guessExtension();
                
                $uploadedImage->move($distinationPath,$newFileName);
                $originalFullFileNamePath = $distinationPath.$newFileName;
                
                $asset =  new \Pimcore\Model\Asset();
                $asset->setParentId(1);
                $asset->setFilename($newFileName);
                $asset->setData(file_get_contents($originalFullFileNamePath));
                $retData        = $asset->save();
                $latestAssetsId = $retData->getId();
                $image          = Asset\Image::getById($latestAssetsId);	
                
                $newObject = DataObject\Product::getById($ObjectId);
                
                $newObject->setName($name);
                $newObject->setColor($color);
                $newObject->setImage($image);
                $newObject->setWorkflowState($workflowState);
                $newObject->save();    
                
                return $this->redirect('/listAction');

            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }   

    public function deleteAction(Request $request): Response    
    {
        try{
                $ObjectId = $request->get('ObjectId'); 
                $myObject = DataObject\Product::getById($ObjectId);
                $myObject->delete();

                return $this->redirect('/list');

            }catch (\Exception $ex) {
                return new JsonResponse([
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine()
                ]);
            }
    }
}
?>