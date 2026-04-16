<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(Request $request, EntityManagerInterface $em): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $repo = $em->getRepository(Article::class);
        $total = (int) $em->createQuery('SELECT COUNT(a.id) FROM App\Entity\Article a')->getSingleScalarResult();
        $articles = $repo->findBy([], ['publishedAt' => 'DESC'], $limit, $offset);
        $pages = (int) ceil($total / $limit);

        // Event sections: top cities + top types
        $conn = $em->getConnection();
        $topCities = $conn->fetchAllAssociative(
            "SELECT e.city, COUNT(*) as cnt FROM event e WHERE e.city IS NOT NULL AND e.city != '' GROUP BY e.city ORDER BY cnt DESC LIMIT 5"
        );
        $topTypes = $conn->fetchAllAssociative(
            "SELECT e.event_type, COUNT(*) as cnt FROM event e GROUP BY e.event_type ORDER BY cnt DESC LIMIT 6"
        );

        // Articles by city (3 per city)
        $citySections = [];
        foreach ($topCities as $row) {
            $cityArticles = $conn->fetchAllAssociative(
                "SELECT a.* FROM article a JOIN event e ON e.article_id = a.id WHERE e.city = ? ORDER BY a.published_at DESC LIMIT 4",
                [$row['city']]
            );
            if ($cityArticles) {
                $citySections[] = ['city' => $row['city'], 'count' => $row['cnt'], 'articles' => $cityArticles];
            }
        }

        // Articles by type (4 per type)
        $typeSections = [];
        $typeLabels = [
            'crime' => 'Faits divers', 'accident' => 'Accidents', 'incendie' => 'Incendies',
            'politique' => 'Politique', 'economie' => 'Économie', 'sport' => 'Sport',
            'technologie' => 'Technologie', 'societe' => 'Société', 'sante' => 'Santé',
            'environnement' => 'Environnement',
        ];
        foreach ($topTypes as $row) {
            $typeArticles = $conn->fetchAllAssociative(
                "SELECT a.* FROM article a JOIN event e ON e.article_id = a.id WHERE e.event_type = ? ORDER BY a.published_at DESC LIMIT 4",
                [$row['event_type']]
            );
            if ($typeArticles) {
                $typeSections[] = [
                    'type' => $row['event_type'],
                    'label' => $typeLabels[$row['event_type']] ?? ucfirst($row['event_type']),
                    'count' => $row['cnt'],
                    'articles' => $typeArticles,
                ];
            }
        }

        $response = $this->render('blog/home.html.twig', [
            'articles' => $articles,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'citySections' => $citySections,
            'typeSections' => $typeSections,
        ]);
        $response->setPublic();
        $response->setMaxAge(600);

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

    #[Route('/type/{eventType}', name: 'app_type')]
    public function type(string $eventType, EntityManagerInterface $em): Response
    {
        $typeLabels = [
            'crime' => 'Faits divers', 'accident' => 'Accidents', 'incendie' => 'Incendies',
            'politique' => 'Politique', 'economie' => 'Économie', 'sport' => 'Sport',
            'technologie' => 'Technologie', 'societe' => 'Société', 'sante' => 'Santé',
            'environnement' => 'Environnement',
        ];

        $conn = $em->getConnection();
        $articles = $conn->fetchAllAssociative(
            "SELECT a.* FROM article a JOIN event e ON e.article_id = a.id WHERE e.event_type = ? ORDER BY a.published_at DESC LIMIT 50",
            [$eventType]
        );

        $label = $typeLabels[$eventType] ?? ucfirst($eventType);

        $response = $this->render('blog/filter.html.twig', [
            'articles' => $articles,
            'filterType' => 'type',
            'filterValue' => $label,
            'filterSlug' => $eventType,
        ]);
        $response->setPublic();
        $response->setMaxAge(1800);

        return $response;
    }

    #[Route('/ville/{city}', name: 'app_city')]
    public function city(string $city, EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $articles = $conn->fetchAllAssociative(
            "SELECT a.* FROM article a JOIN event e ON e.article_id = a.id WHERE e.city = ? ORDER BY a.published_at DESC LIMIT 50",
            [$city]
        );

        $response = $this->render('blog/filter.html.twig', [
            'articles' => $articles,
            'filterType' => 'ville',
            'filterValue' => $city,
            'filterSlug' => $city,
        ]);
        $response->setPublic();
        $response->setMaxAge(1800);

        return $response;
    }

    #[Route('/sitemap.xml', name: 'app_sitemap')]
    public function sitemap(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findAll();
        $conn = $em->getConnection();

        // Cities with at least 2 articles → worth a dedicated page in the sitemap
        $cities = $conn->fetchAllAssociative(
            "SELECT city, COUNT(*) as cnt FROM event WHERE city IS NOT NULL AND city != '' GROUP BY city HAVING cnt >= 2 ORDER BY cnt DESC"
        );
        $types = $conn->fetchFirstColumn(
            "SELECT DISTINCT event_type FROM event WHERE event_type IS NOT NULL AND event_type != ''"
        );

        $response = $this->render('blog/sitemap.xml.twig', [
            'articles' => $articles,
            'cities' => $cities,
            'types' => $types,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        $response->setPublic();
        $response->setMaxAge(86400);
        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots')]
    public function robots(): Response
    {
        return new Response(
            "User-agent: *\nAllow: /\n",
            200,
            ['Content-Type' => 'text/plain', 'Cache-Control' => 'public, max-age=86400'],
        );
    }

    #[Route('/{slug}', name: 'app_article')]
    public function article(string $slug, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException();
        }

        $tickerArticles = $em->getRepository(Article::class)->findBy(
            ['category' => 'actualites'],
            ['publishedAt' => 'DESC'],
            15,
        );

        // Get event info for this article
        $event = $em->getRepository(Event::class)->findOneBy(['article' => $article]);

        // Related articles — same city (widget "Autres actualités à X" en bas de page)
        $relatedCity = [];
        $city = $article->getCity() ?: ($event ? $event->getCity() : null);
        if ($city) {
            $relatedCity = $em->getConnection()->fetchAllAssociative(
                "SELECT a.id, a.title, a.slug, a.image_url, a.published_at, a.excerpt
                 FROM article a
                 LEFT JOIN event e ON e.article_id = a.id
                 WHERE (a.city = ? OR e.city = ?) AND a.id != ?
                 ORDER BY a.published_at DESC LIMIT 6",
                [$city, $city, $article->getId()]
            );
        }

        $response = $this->render('blog/article.html.twig', [
            'article' => $article,
            'tickerArticles' => $tickerArticles,
            'event' => $event,
            'relatedCity' => $relatedCity,
            'articleCity' => $city,
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
        ]);
        $response->setPublic();
        $response->setMaxAge(1800);

        return $response;
    }
}
