<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Inventory;
use App\Entity\Subwoofer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const ROD_INV_REF = 'inv-rod';
    private const COCO_INV_REF = 'inv-coco';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    private function subwoofersGenerator(): \Generator
    {
        // [Modèle, Marque, Année, Référence de l'inventaire]
        yield ['KS21', 'L-Acoustics', 2019, self::ROD_INV_REF];
        yield ['SB18', 'L-Acoustics', 2010, self::ROD_INV_REF];
        yield ['KS118', 'QSC', 2019, self::ROD_INV_REF];
        yield ['Linear Sub 2000 A', 'HK Audio', 2012, self::COCO_INV_REF];
        yield ['KS28', 'L-Acoustics', 2016, self::COCO_INV_REF];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Création de Rod et de son inventaire simultanément
        $invRod = new Inventory();
        $invRod->setDescription("La collection de subs de Rod");
        
        $rod = new Member();
        $rod->setEmail('r.nouelwacrenier@icloud.com');
        $rod->setPassword($this->hasher->hashPassword($rod, '0000'));
        
        $rod->setInventory($invRod); 
        
        $manager->persist($invRod);
        $manager->persist($rod);
        $this->addReference(self::ROD_INV_REF, $invRod);

        // 2. Création de Coco et de son inventaire simultanément
        $invCoco = new Inventory();
        $invCoco->setDescription("La collection de subs de Coco");
        
        $coco = new Member();
        $coco->setEmail('corentin.cloarec@telecom-sudparis.eu');
        $coco->setPassword($this->hasher->hashPassword($coco, '0000'));
        
        $coco->setInventory($invCoco);
        
        $manager->persist($invCoco);
        $manager->persist($coco);
        $this->addReference(self::COCO_INV_REF, $invCoco);

        // 3. Création des caissons de basse
        foreach ($this->subwoofersGenerator() as [$model, $brand, $year, $invRef]) {
            $subwoofer = new Subwoofer();
            $subwoofer->setModel($model);
            $subwoofer->setBrand($brand);
            $subwoofer->setYear($year);
            
            $inventory = $this->getReference($invRef, Inventory::class);
            $inventory->addSubwoofer($subwoofer);
            
            $manager->persist($subwoofer);
        }
        
        // 4. Un seul flush() à la fin pour tout insérer d'un coup dans le bon ordre !
        $manager->flush();
    }
}