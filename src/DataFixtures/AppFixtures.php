<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Inventory;
use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const OLIVIER_INV_REF = 'inv-olivier';
    private const SLASH_INV_REF = 'inv-slash';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    private function albumsGenerator(): \Generator
    {
        // [Titre, Artiste, Année, Label, Référence de l'inventaire]
        yield ['Dark Side of the Moon', 'Pink Floyd', 1973, 'Harvest', self::OLIVIER_INV_REF];
        yield ['Rumours', 'Fleetwood Mac', 1977, 'Warner Bros.', self::OLIVIER_INV_REF];
        yield ['Appetite for Destruction', 'Guns N\' Roses', 1987, 'Geffen', self::SLASH_INV_REF];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création d'Olivier et de son inventaire simultanément
        $invOlivier = new Inventory();
        $invOlivier->setDescription("La collection rock d'Olivier");
        
        $olivier = new Member();
        $olivier->setEmail('olivier@localhost');
        $olivier->setPassword($this->hasher->hashPassword($olivier, '123456'));
        
        // On lie l'inventaire au membre AVANT la sauvegarde (corrige l'erreur NOT NULL)
        $olivier->setInventory($invOlivier); 
        
        $manager->persist($invOlivier);
        $manager->persist($olivier);
        $this->addReference(self::OLIVIER_INV_REF, $invOlivier);

        // 2. Création de Slash et de son inventaire simultanément
        $invSlash = new Inventory();
        $invSlash->setDescription("Le trésor de Slash");
        
        $slash = new Member();
        $slash->setEmail('slash@localhost');
        $slash->setPassword($this->hasher->hashPassword($slash, '123456'));
        
        $slash->setInventory($invSlash);
        
        $manager->persist($invSlash);
        $manager->persist($slash);
        $this->addReference(self::SLASH_INV_REF, $invSlash);

        // 3. Création des Albums
        foreach ($this->albumsGenerator() as [$title, $artist, $year, $label, $invRef]) {
            $album = new Album();
            $album->setTitle($title); 
            $album->setArtist($artist);
            $album->setYear($year);
            $album->setLabel($label);
            
            $inventory = $this->getReference($invRef, Inventory::class);
            $inventory->addAlbum($album);
            
            $manager->persist($album);
        }
        
        // 4. Un seul flush() à la fin pour tout insérer d'un coup dans le bon ordre !
        $manager->flush();
    }
}