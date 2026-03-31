<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    public function __construct(
        private string $blogDomain,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function home(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(
            [],
            ['publishedAt' => 'DESC'],
            20,
        );

        $response = $this->render('blog/home.html.twig', [
            'articles' => $articles,
            'domain' => $this->blogDomain,
        ]);
        $response->setPublic();
        $response->setMaxAge(1800);

        return $response;
    }

    #[Route('/guides', name: 'app_category_evergreen')]
    public function evergreen(EntityManagerInterface $em): Response
    {
        return $this->categoryPage($em, 'evergreen', 'Guides');
    }

    #[Route('/actualites', name: 'app_category_actualites')]
    public function actualites(EntityManagerInterface $em): Response
    {
        return $this->categoryPage($em, 'actualites', 'Actualités');
    }

    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function sitemap(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findAll();

        $response = $this->render('blog/sitemap.xml.twig', [
            'articles' => $articles,
            'domain' => $this->blogDomain,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots')]
    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nSitemap: https://{$this->blogDomain}/sitemap.xml";

        return new Response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    #[Route('/{slug}', name: 'app_article')]
    public function article(string $slug, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException();
        }

        $response = $this->render('blog/article.html.twig', [
            'article' => $article,
            'domain' => $this->blogDomain,
        ]);
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }

    private function categoryPage(EntityManagerInterface $em, string $category, string $name): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(
            ['category' => $category],
            ['publishedAt' => 'DESC'],
        );

        $response = $this->render('blog/category.html.twig', [
            'articles' => $articles,
            'categoryName' => $name,
            'categorySlug' => $category === 'evergreen' ? 'guides' : 'actualites',
            'domain' => $this->blogDomain,
        ]);
        $response->setPublic();
        $response->setMaxAge(1800);

        return $response;
    }
}
