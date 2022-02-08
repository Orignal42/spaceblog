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
        return $this->render('blog_post/index.html.twig', [
            'blog_posts' => $blogPostRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="blog_post_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);
        $cover = $form->get('cover')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($cover) {
                $blogPost->setCover($this->uploadFile($cover, $slugger, 'cover_directory'));
            }

            $blogPost->setCreatedAt(new DatetimeImmutable());
            $entityManager->persist($blogPost);
            $entityManager->flush();

            return $this->redirectToRoute('blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('blog_post/new.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="blog_post_show", methods={"GET"})
     */
    public function show(BlogPost $blogPost): Response
    {
        return $this->render('blog_post/show.html.twig', [
            'blog_post' => $blogPost,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="blog_post_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, BlogPost $blogPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('blog_post/edit.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="blog_post_delete", methods={"POST"})
     */
    public function delete(Request $request, BlogPost $blogPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $blogPost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blogPost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('blog_post_index', [], Response::HTTP_SEE_OTHER);
    }

    public function uploadFile($file, $slugger, $targetDirectory)
    {

        if ($file) {

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            try {
                $file->move(
                    $this->getParameter($targetDirectory),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            return $newFilename;
        }
    }
}
