<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use DatetimeImmutable;

/**
 * @Route("/blog/post")
 */
class BlogPostController extends AbstractController
{
    /**
     * @Route("/", name="blog_post_index", methods={"GET"})
     */
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        $blogPost= $blogPostRepository->findBy([],['created_at'=>'DESC']);
        // dd($blogPost);
        return $this->render('blog_post/index.html.twig', [
            'blog_posts' => $blogPost,
        ]);
    }


    /**
     * @Route("/{id}", name="blog_post_show", methods={"GET"})
     */
    public function show($id,BlogPostRepository $blogPostRepository): Response
    {
       $blogPost= $blogPostRepository->findBySlug($id);
  

        return $this->render('blog_post/show.html.twig', [
            'blog_post' => $blogPost,
        ]);
    }





}


