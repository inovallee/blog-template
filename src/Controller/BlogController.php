<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(
            [],
            ['publishedAt' => 'DESC'],
            20,
        );

        return $this->render('blog/home.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/evergreen', name: 'app_category_evergreen')]
    public function evergreen(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(
            ['category' => 'evergreen'],
            ['publishedAt' => 'DESC'],
        );

        return $this->render('blog/category.html.twig', [
            'articles' => $articles,
            'categoryName' => 'Guides',
            'categorySlug' => 'evergreen',
        ]);
    }

    #[Route('/actualites', name: 'app_category_actualites')]
    public function actualites(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(
            ['category' => 'actualites'],
            ['publishedAt' => 'DESC'],
        );

        return $this->render('blog/category.html.twig', [
            'articles' => $articles,
            'categoryName' => 'Actualités',
            'categorySlug' => 'actualites',
        ]);
    }

    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function sitemap(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findAll();
        $domain = $_SERVER['HTTP_HOST'] ?? 'blog.example.com';

        $response = $this->render('blog/sitemap.xml.twig', [
            'articles' => $articles,
            'domain' => $domain,
        ]);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    #[Route('/{slug}', name: 'app_article')]
    public function article(string $slug, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException();
        }

        return $this->render('blog/article.html.twig', [
            'article' => $article,
        ]);
    }
}
