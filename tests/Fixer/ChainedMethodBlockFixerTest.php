<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\EasyCodingStandard\Tests\Fixer;

use Contao\EasyCodingStandard\Fixer\ChainedMethodBlockFixer;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

class ChainedMethodBlockFixerTest extends TestCase
{
    /**
     * @dataProvider getCodeSamples
     */
    public function testFixesTheCode(string $code, string $expected): void
    {
        $tokens = Tokens::fromCode($code);

        $fixer = new ChainedMethodBlockFixer();
        $fixer->fix($this->createMock('SplFileInfo'), $tokens);

        $this->assertSame($expected, $tokens->generateCode());
    }

    public function getCodeSamples(): \Generator
    {
        yield [
            <<<'EOT'
                <?php

                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase
                {
                    public function testFoo(): void
                    {
                        $mock = $this->createMock(Foo::class);
                        $mock
                            ->method("isFoo")
                            ->willReturn(true)
                        ;
                        $mock
                            ->method("isBar")
                            ->willReturn(false)
                        ;

                        $mock->isFoo();

                        if (true) {
                            $mock
                                ->method("isBaz")
                                ->willReturn(false)
                            ;
                        }

                        $mock
                            ->method("isBat")
                            ->willReturnCallback(
                                function () {
                                    $bar = $this->createMock(Bar::class);
                                    $bar
                                        ->method("isFoo")
                                        ->willReturn(false)
                                    ;
                                    $bar
                                        ->method("isBar")
                                        ->willReturn(true)
                                    ;
                                }
                            )
                        ;
                    }
                }
                EOT,
            <<<'EOT'
                <?php

                use PHPUnit\Framework\TestCase;

                class SomeTest extends TestCase
                {
                    public function testFoo(): void
                    {
                        $mock = $this->createMock(Foo::class);
                        $mock
                            ->method("isFoo")
                            ->willReturn(true)
                        ;

                        $mock
                            ->method("isBar")
                            ->willReturn(false)
                        ;

                        $mock->isFoo();

                        if (true) {
                            $mock
                                ->method("isBaz")
                                ->willReturn(false)
                            ;
                        }

                        $mock
                            ->method("isBat")
                            ->willReturnCallback(
                                function () {
                                    $bar = $this->createMock(Bar::class);
                                    $bar
                                        ->method("isFoo")
                                        ->willReturn(false)
                                    ;

                                    $bar
                                        ->method("isBar")
                                        ->willReturn(true)
                                    ;
                                }
                            )
                        ;
                    }
                }
                EOT,
        ];
    }
}
