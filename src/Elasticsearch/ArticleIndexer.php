<?php

namespace App\Elasticsearch;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Client;
use Elastica\Document;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArticleIndexer
{
    private $client;
    private $articleRepository;
    private $router;

    public function __construct(Client $client, ArticleRepository $articleRepository, UrlGeneratorInterface $router)
    {
        $this->client = $client;
        $this->articleRepository = $articleRepository;
        $this->router = $router;
    }

    public function buildDocument(Article $article)
    {
        $summary = mb_substr($article->getContent(), 0, 160);
        $linkedBrands = implode(' ,', $article->getLinkedBrand()->getValues());
        $linkedCategories = implode(' ,', $article->getLinkedCategory()->getValues());

        return new Document(
            $article->getId(), // Manually defined ID
            [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'content' => $article->getContent(),
                'price' => $article->getPrice(),
                'linkedBrands' => $linkedBrands,
                'linkedCategories' => mb_strtoupper($linkedCategories),
                'linkedImage' => $article->getLinkedImage()->getImage(),
                'summary'=> $summary,
                //                'date' => $article->getcreatedAt()->format('M d, Y'),
//                'url' => $this->router->generate('blog_article', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_PATH),
                // Not indexed but needed for display

            ],
            "articles" // Types are deprecated, to be removed in Elastic 7
        );
    }

    public function indexAllDocuments($indexName)
    {

//        $queryBuilder = $this->entityManager->createQueryBuilder()
//            ->select('COUNT(e)')
//            ->from('App\Entity\Article', 'e');
//
//        $count = $queryBuilder->getQuery()->getSingleScalarResult();
//
//        $range = 100;
//
//        for ($i = 0; $i <= $count; $i++) {
//            $offset = $i;
//
//            $q = $this->entityManager->createQuery('select u from App\Entity\Article u')->setFirstResult( $offset )->setMaxResults( $range );
//            $index = $this->client->getIndex($indexName);
//            $batchSize = 20;
//            $i = 0;
//            $iterableResult = $q->iterate();
//            foreach ($iterableResult as $row) {
//                $post = $row[0];
//                $documents[] = $this->buildDocument($post);
//                if (($i % $batchSize) === 0) {
//                    $index->addDocuments($documents);
//                    $index->refresh();
//                }
//                ++$i;
//            }
//
//
//            $i += $range;
//        }

        $allPosts = $this->articleRepository->findAll();

        $index = $this->client->getIndex($indexName);

        $documents = [];
        foreach ($allPosts as $post) {
            $documents[] = $this->buildDocument($post);
        }

        $index->addDocuments($documents);
        $index->refresh();
    }
}