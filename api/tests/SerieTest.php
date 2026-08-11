<?php

namespace App\Tests;

use App\Entity\Serie;
use PHPUnit\Framework\TestCase;

class SerieTest extends TestCase
{
    /**
     * Test de la formule d'Epley : 1RM = poids × (1 + reps / 30)
     */
    public function testCalculer1RM(): void
    {
        $serie = new Serie();
        $serie->setPoidsKg(80);
        $serie->setNbRepsRealisees(8);

        // 80 × (1 + 8/30) = 80 × 1.2667 = 101.33
        $rm1 = $serie->calculer1RM();
        $this->assertEqualsWithDelta(101.3, $rm1, 0.5);
    }

    public function testCalculer1RMAvecUneRep(): void
    {
        $serie = new Serie();
        $serie->setPoidsKg(100);
        $serie->setNbRepsRealisees(1);

        // 1 rep = le poids lui-même
        $rm1 = $serie->calculer1RM();
        $this->assertEqualsWithDelta(100.0, $rm1, 0.1);
    }

    public function testCalculer1RMAvecPoidsElevé(): void
    {
        $serie = new Serie();
        $serie->setPoidsKg(120);
        $serie->setNbRepsRealisees(5);

        // 120 × (1 + 5/30) = 120 × 1.1667 = 140
        $rm1 = $serie->calculer1RM();
        $this->assertEqualsWithDelta(140.0, $rm1, 0.5);
    }

    public function testEstPrDefautFalse(): void
    {
        $serie = new Serie();
        $this->assertFalse($serie->isEstPr());
    }

    public function testSetEstPr(): void
    {
        $serie = new Serie();
        $serie->setEstPr(true);
        $this->assertTrue($serie->isEstPr());
    }
}