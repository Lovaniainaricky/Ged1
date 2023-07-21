<?php

namespace App\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("", name="app_upload_")
 */
class UploadController extends AbstractController
{
    /**
     * @Route("/media", name="index")
     */
    public function index(): Response
    {
        return $this->render('upload/index.html.twig', [
            'controller_name' => 'UploadController',
        ]);
    }

    /**
     * @Route("/app_upload_type/{type}",name="list_type")
     */
    public function listFileInFolder(Request $request) : Response {

        $folderPath = $request->get("type").'/';
        $filesystem = new Filesystem();
        $filesystem->mkdir($folderPath, 0777);
        
        $finder = new Finder();
        $files = $finder->files()->in($folderPath);
        $type = $request->get("type");

        return $this->render('upload/type_list.html.twig',compact('files','type'));
    }

    /**
     * @Route("/detele_file/{type}/{filename}",name="detele_file")
     */
    public function deleteFile(Request $request) : Response {

        $filepath = $request->get('type')."/".$request->get('filename');
        
        $filesystem = new Filesystem();
        if($filesystem->exists($filepath))
        {
            $filesystem->remove($filepath);    
        }

        return $this->redirectToRoute('app_upload_index');
    }

    /**
     * @Route("/download_file/{type}/{filename}",name="download_file")
     */
    public function telechargerFichier(Request $request): BinaryFileResponse
    {
        $filePath = $request->get('type')."/".$request->get('filename');
        $fileName = $request->get('filename');

        return $this->file($filePath, $fileName , ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    /**
     * @Route("/upload_file/{type}",name="_file")
     */
    public function uploadFichier(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('uploadFile');
            $type = $request->get('type');

            switch ($type) {
                case 'image':
                    $typeMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    $textErreur = "image (JPEG, PNG, GIF)";
                    break;
                case 'video':
                    $typeMimes = ['video/mp4',"video/x-mpeg","audio/mpeg"];
                    $textErreur = "Video (Mp4)";
                    break;
                case 'pdf':
                    $typeMimes = ['application/pdf'];
                    $textErreur = "Pdf ";
                    break;
                
                default:
                    $typeMimes = [];
                    break;
            }

            if ($uploadedFile)
            { 
                $file = new File($uploadedFile);
                

                if (!empty($typeMimes) && !in_array($file->getMimeType(), $typeMimes))
                {
                    $this->addFlash('error', 'Seuls les fichiers '.$textErreur.' sont autorisés.');
                    return $this->redirectToRoute('app_upload_index');
                }

                $filename = $uploadedFile->getClientOriginalName();
                $filesystem = new Filesystem();
                if($filesystem->exists($type."/".$filename))
                {
                    // $filename = $filename .
                    //nouveau nom
                }
                $uploadedFile->move($type, $filename);

                return $this->redirectToRoute('app_upload_index');
            }
        }
        
        return $this->redirectToRoute("app_upload_index");
    }

}
