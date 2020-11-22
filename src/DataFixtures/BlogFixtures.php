<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BlogFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
       
        for ($i = 1; $i < 50; $i++) {
            $article = new Article();
            $article->setTitle("Title".$i);
            $article->setContent("Contenu article".$i);
            $author = new Author();
            $author->setFullname("foulen1" . $i);
            $author->setBiography("biographie".$i);
            $manager->persist($author);
            $article->setAuthor($author);
            $manager->persist($article);
        }        

        $manager->flush();
    }
}

